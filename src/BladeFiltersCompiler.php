<?php

namespace Pine\BladeFilters;

class BladeFiltersCompiler
{
    protected static $containers = [];

    /**
     * Compile the echo statements.
     *
     * @param  string  $value
     * @return string
     */
    public function compile($value)
    {
        return preg_replace_callback('/(?<=((?<!@){{))(.*?)(?=}})/mu', function ($matches) {
            return $this->parseFilters($matches[0]);
        }, $value);
    }

    /**
     * Parse the blade filters.
     *
     * @param  string  $value
     * @return string
     */
    protected function parseFilters($value)
    {
        if (! preg_match('/(?=(?:[^\'\"\`)]*([\'\"\`])[^\'\"\`]*\1)*[^\'\"\`)]*$)(\|.*)/u', $value, $matches)) {
            return $value;
        }

        $filters = preg_split('/\|(?=(?:[^\'\"\`]*([\'\"\`])[^\'\"\`]*\1)*[^\'\"\`]*$)/u', $matches[0]);

        if (empty($filters = array_values(array_filter(array_map('trim', $filters))))) {
            return $value;
        }


        $wrapped = '';

        foreach ($filters as $key => $filter) {
            $filter = preg_split('/:(?=(?:[^\'\"\`]*([\'\"\`])[^\'\"\`]*\1)*[^\'\"\`]*$)/u', trim($filter));

            $filterName =  $filter[0];

            $containered = isset(self::$containers[$filterName]) ? call_user_func(self::$containers[$filterName], $filterName): BladeFilters::class.'::';

            $wrapped = sprintf(
                $containered.'%s(%s%s)',
                $filterName,
                $key === 0 ? trim(str_replace($matches[0], '', $value)) : $wrapped,
                isset($filter[1]) ? ",{$filter[1]}" : ''
            );
        }

        return $wrapped;
    }

    public static function extend($name, callable $container)
    {
        self::$containers[$name] = $container;
    }
}