<?php

namespace Pine\BladeFilters;

use Pine\BladeFilters\Exceptions\SyntaxException;

class BladeFilterParser
{
    private BladeFilterLexer $lexer;

    private $input;

    /**
     * @param string $input
     */
    public function __construct()
    {
        $this->lexer = new BladeFilterLexer();
    }

    public function parse($input)
    {
        $this->input = $input;

        $this->lexer->setInput($input);

        $this->lexer->moveNext();

        $preFiltered = $this->collectPreFiltered($input);

        $filters = [];
        while ($this->lexer->isNextToken(BladeFilterLexer::T_PIPE)) {
            $this->lexer->moveNext(); // Skip pipe

            $this->Filters($filters);
        }

        return [
            'prefiltered' => $preFiltered,
            'filters' => $filters
        ];
    }

    /**
     * Get the value being filtered
     *
     * @param string $input  example: '"css/carousel.css" | stylesheet_tag'
     *
     * @return mixed   return '"css/carousel.css"'
     */
    private function collectPreFiltered(string $input)
    {
        $this->lexer->skipUntil(BladeFilterLexer::T_PIPE);
        if (null === $this->lexer->lookahead) {
            return $input;
        }

        $preFiltered = trim($this->lexer->getInputUntilPosition($this->lexer->lookahead['position']));
        if ($preFiltered && $preFiltered[0] === '(') {
            $preFiltered = trim($preFiltered, '()');
        }

        return $preFiltered;
    }

    private function Filters(&$filters)
    {
        $this->syntaxErrorIf(null === $this->lexer->lookahead, sprintf('No filter found'));
        $this->match(BladeFilterLexer::T_VARIABLE_NAME, sprintf('Filter name is not valid, "%s"', $this->lexer->lookahead['value']));

        $filter = [];
        $filter['name'] = $this->lexer->token['value'];
        $filterArguments = [];
        $filter['arguments'] = &$filterArguments;
        $filters[] = $filter;

        $next = $this->lexer->lookahead;
        if (null == $next) {
            return;
        }

        if($next['type'] == BladeFilterLexer::T_COLON) {
            $this->lexer->moveNext(); //Skip to colon

            list($argName, $argValue) = $this->collectFilterArgument();
            $filterArguments[$argName] = $argValue;

            $this->lexer->moveNext();

            while($this->lexer->token !== null) {
                $type = $this->lexer->token['type'];
                if($type === BladeFilterLexer::T_COMMA) {
                    list($argName, $argValue) = $this->collectFilterArgument();
                    $filterArguments[$argName] = $argValue;
                } else if($type === BladeFilterLexer::T_PIPE) {
                    $this->Filters($filters);
                } else if (null !== $this->lexer->token) {
                    $this->syntaxErrorIf(true,
                        sprintf('It supposed to be another argument, or filter, but "%s" given', $this->lexer->token['value'],
                        )
                    );
                }

                $this->lexer->moveNext();
            }
        } else if ($next['type'] == BladeFilterLexer::T_PIPE) {
            $this->lexer->moveNext(); //Skip to pipe

            $this->Filters($filters);
        } else if (null !== $next){
            $this->syntaxErrorIf(true, sprintf(
                'It supposed to be either arguments after filter or another filter, but "%s" given',
                $next['value']
                ));
        }
    }

    /**
     * Collect filter argument,
     *
     * @return array<string, string, bool>  tuple, argument name, argument value
     */
    private function collectFilterArgument(): array
    {
        // Argument name
        $next = $this->lexer->lookahead;
        $this->syntaxErrorIf(null === $next,
            sprintf('No arguments found after %s', $this->lexer->token['value'])
        );
        $this->syntaxErrorIf(BladeFilterLexer::T_PIPE === $next['type'],
            sprintf('No argument found')
        );
        $this->syntaxErrorIf(BladeFilterLexer::T_VARIABLE_NAME !== $next['type'],
            sprintf('The argument name must be literal, however "%s" given',$next['value'])
        );

        $this->lexer->moveNext();
        $argumentName = $this->lexer->token['value'];

        // Equal sign
        $this->match(
            BladeFilterLexer::T_EQUALS,
            sprintf('No equal sign found after argument "%s"',$argumentName)
        );

        // Argument value
        $token = $this->lexer->lookahead;
        $this->syntaxErrorIf(null === $token,
            sprintf('No value specified for argument "%s"',$argumentName)
        );
        $isValidArgumentValue = $this->lexer->isNextTokenAny([
            BladeFilterLexer::T_INTEGER, 
            BladeFilterLexer::T_STRING,
            BladeFilterLexer::T_FLOAT,
            BladeFilterLexer::T_VARIABLE_EXPRESSION,
        ]);

        $this->syntaxErrorIf(!$isValidArgumentValue,
            sprintf(' The value of filter argument "%s" is not valid, it supposed to be string, integer, float or variable, got %s', 
            $argumentName,
            $token['value']
        ));
        $this->lexer->moveNext();

        return [$argumentName,  $token['value']];
    }

    private function syntaxErrorIf(bool $predicate, string $errMessage, ?array $token = null)
    {
        $errMessage = sprintf('%s for filter "%s"', $errMessage, $this->input);

        if ($token === null) {
            $token = $this->lexer->lookahead;
        }
        if (null !== $token) {
            $errMessage = sprintf('%s at position %s',$errMessage, $token['position'] );
        }

        if ($predicate) {
            throw new SyntaxException($errMessage);
        }
    }

    private function match(int $token, $message): bool
    {
        if (!$this->lexer->isNextToken($token)) {
            throw $this->syntaxErrorIf(true, $message);
        }

        return $this->lexer->moveNext();
    }
}
