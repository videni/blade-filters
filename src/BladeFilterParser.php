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

        $prefiltered = $this->collectPreFiltered($input);

        $filters = [];
        while ($this->lexer->isNextToken(BladeFilterLexer::T_PIPE)) {
            $this->lexer->moveNext(); //skip pipe
           $this->Filters($filters);
        }

        return [
            'prefitered' => $prefiltered,
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

        $prefiltered = trim($this->lexer->getInputUntilPosition($this->lexer->lookahead['position']));
        if ($prefiltered && $prefiltered[0] === '(') {
            $prefiltered = trim($prefiltered, '()');
        }

        return $prefiltered;
    }

    private function Filters(&$filters)
    {
        $this->lexer->moveNext();

        $this->syntaxErrorIf(null === $this->lexer->token, sprintf('No filter found'));

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
            $this->lexer->moveNext();

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
                        sprintf('It supposed to be another argument, or filter, but %s given', $this->lexer->token['value'],
                        )
                    );
                }

                $this->lexer->moveNext();
            }
        } else if ($next['type'] == BladeFilterLexer::T_PIPE) {
            $this->lexer->moveNext();

            $this->Filters($filters);
        } else if (null !== $next){
            $this->syntaxErrorIf(true, sprintf(
                'It supposed to be either arguments after filter or another filter, but %s given',
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
        $this->lexer->moveNext();

        // Argument name
        $token = $this->lexer->token;
        $this->syntaxErrorIf(null === $token,
            sprintf('No arguments found after ":"')
        );
        $this->syntaxErrorIf(BladeFilterLexer::T_PIPE === $token['type'],
            sprintf('No argument found')
        );
        $this->syntaxErrorIf(BladeFilterLexer::T_LITERAL !== $token['type'],
            sprintf('The argument name must be literal, however %s given, for filter %s',
                $token['value'],
                $this->input,
            )
        );
        $argumentName = $token['value'];

        // Equal sign
        $this->match(
            BladeFilterLexer::T_EQUALS,
            sprintf('No equal sign found after argument %s',$argumentName)
        );

        // Argument value
        $this->lexer->moveNext();
        $token = $this->lexer->token;
        $this->syntaxErrorIf(null === $token,
            sprintf('No value specified for argument %s',$argumentName)
        );
        $argumentValue = $token['value'];

        return [$argumentName, $argumentValue];
    }

    private function syntaxErrorIf(bool $predicate, string $errMessage, ?array $token = null)
    {
        $errMessage = sprintf('%s for filter "%s"', $errMessage, $this->input);

        if ($token === null) {
            $token = $this->lexer->token;
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
        if (! $this->lexer->isNextToken($token)) {
            throw $this->syntaxErrorIf(true, $this->lexer->getLiteral($token), $message);
        }

        return $this->lexer->moveNext();
    }
}
