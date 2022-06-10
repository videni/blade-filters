<?php

namespace Pine\BladeFilters;

use Doctrine\Common\Lexer\AbstractLexer;

class BladeFilterLexer extends AbstractLexer
{
    /**
     * All tokens that are not valid identifiers must be < 100
     */
    public const T_NONE = 1;
    public const T_VARIABLE_NAME = 2;
    public const T_STRING = 3;
    public const T_QUOTE = 4;
    public const T_INTEGER = 5;
    public const T_FLOAT = 6;
    public const T_VARIABLE_EXPRESSION = 7;

    /**
     * All tokens that are also identifiers should be >= 100,
     */
    public const T_OPEN_PARENTHESIS    = 100;
    public const T_CLOSE_PARENTHESIS   = 101;
    public const T_EQUALS = 102;
    public const T_COLON = 103;
    public const T_COMMA = 104;
    public const T_PIPE = 105;
    public const T_TRUE                = 106;
    public const T_NULL                = 107;
    public const T_FALSE               = 108;

    /** @var array<string, int> */
    protected $specials = [
        'true'  => self::T_TRUE,
        'false' => self::T_FALSE,
        'null'  => self::T_NULL,
    ];

    /**
     * @inheritdoc
     */
    protected function getCatchablePatterns()
    {
        return [
            '\'(?:\\\\\'|.)*?\'',
            // Single quoted string

            '"(?:\\\\\"|.)*?"',
            // Double quoted string

            '\(.*?\)',
            // Expression

            '[a-z_][a-z0-9_]*',
            // Safe string, filter name, filter argument name must be safe string

            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?',
            // Integer, float

            '\$[a-z_][a-z0-9_]*(?:->[a-z_][a-z0-9_]*)*',
            // A variable expression, 变量表达式
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getNonCatchablePatterns()
    {
        /**
         * whitespace
         */
        return ['\s+', '(.)'];
    }

     /**
     * @inheritdoc
     */
    protected function getType(&$value)
    {
        $lowerValue = strtolower($value);
        if (isset($this->specials[$lowerValue])) {
            return $this->specials[$lowerValue];
        }

        switch (true) {
            /**
             * Recognize numeric values
             */
            case (is_numeric($value)):
                if (strpos($value, '.') !== false || stripos($value, 'e') !== false) {
                    return self::T_FLOAT;
                }

                return self::T_INTEGER;

            /**
             * Recognize quoted strings
             */
            case ($value[0] === '\'' || $value[0] == '"'):
                return self::T_STRING;
            
             /**
             * Recognize variable name, can be valid filter name, filter argument_name
             */    
            case preg_match('/^[a-z_][a-z0-9_]*/', $value):
                return self::T_VARIABLE_NAME;
            
            /**
             *  Recognize variables
             */
            case preg_match('/^\$[a-z_][a-z0-9_]*(?:->[a-z_][a-z0-9_]*)*/', $value):
                return self::T_VARIABLE_EXPRESSION;

            /**
             * Recognize identifiers
             */
            case ($value === '|'):
                return self::T_PIPE;
            case ($value === ':'):
                return self::T_COLON;
            case ($value === ','):
                return self::T_COMMA;
            case ($value === '='):
                return self::T_EQUALS;
                
            default:
                return self::T_NONE;;
        }
    }
}
