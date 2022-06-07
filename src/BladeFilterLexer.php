<?php

namespace Pine\BladeFilters;

use Doctrine\Common\Lexer\AbstractLexer;

class BladeFilterLexer extends AbstractLexer
{
    /**
     * All tokens that are not valid identifiers must be < 100
     */
    public const T_NONE = 1;
    public const T_STRING = 2;
    public const T_VARIABLE = 7;
    public const T_LITERAL = 8;
    public const T_INTEGER = 9;
    public const T_FLOAT = 10;

    /**
     * All tokens that are also identifiers should be >= 100,
     */
    public const T_OPEN_PARENTHESIS    = 100;
    public const T_CLOSE_PARENTHESIS   = 101;
    public const T_EQUALS = 102;
    public const T_COLON = 103;
    public const T_COMMA = 104;
    public const T_PIPE = 105;

    /**
     * @inheritdoc
     */
    protected function getCatchablePatterns()
    {
        return [
            '\(.*?\)',
            //Expression

            '[a-z_\\\][a-z0-9_]*[a-z0-9_]{1}',
            // safe string

            "[\'\"](?:[^'\"]|'')*[\'\"]",
            // single or double quoted string

            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?',
            //integer, float

            '\$[a-z_][a-z0-9_]*(?:->[a-z_][a-z0-9_]*)*',
            // a variable
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
        return ['\s+'];
    }

     /**
     * @inheritdoc
     */
    protected function getType(&$value)
    {
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
             *  Recognize variables
             */
            case ($value[0] === '$'):
                return self::T_VARIABLE;

            /**
             * Recognize symbols
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
                return self::T_LITERAL;;
        }
    }
}
