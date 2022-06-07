<?php

namespace Pine\BladeFilters;

use Pine\BladeFilters\Exceptions\MissingBladeFilterException;
use Pine\BladeFilters\FilterProvider\BladeFilterProviderRegistry;

class BladeFiltersCompiler
{
    protected BladeFilterParser $parser;

    protected BladeFilterProviderRegistry $registry;

    public function __construct(BladeFilterProviderRegistry $registry)
    {
        $this->parser = new BladeFilterParser();
        $this->registry = $registry;
    }

    /**
     * Compile the echo statements.
     *
     * @param  string  $value
     * @return string
     */
    public function compile($value)
    {
        return preg_replace_callback('/(?<=((?<!@){{))(.*?)(?=}})/mu', function ($matches) {
            return $this->compileFilters($matches[0]);
        }, $value);
    }

    /**
     * Parse the blade filters.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileFilters($value)
    {
        $filterExpression = $this->parser->parse($value);
        if (empty($filterExpression['filters'])) {
            return $value;
        }

        $prefitered = $filterExpression['prefitered'];
        $filters = $filterExpression['filters'];

        $wrapped = '';
        $first = array_shift($filters);

        $wrapped = sprintf(
            $this->getContainer($first['name']).'%s(%s,%s)',
            $first['name'],
            $prefitered,
            $this->stringifyArguments($first['name'], $first['arguments'])
        );

        foreach ($filters as $filter) {
            $filterName = $filter['name'];
            $arguments = $filter['arguments'];

            $wrapped = sprintf(
                $this->getContainer($filterName).'%s(%s,%s)',
                $filterName,
                $wrapped,
                $this->stringifyArguments($filterName, $arguments)
            );
        }

        return $wrapped;
    }

    private function stringifyArguments(string $filterName, array $arguments): string
    {
        foreach($this->registry->all() as $filterProvider) {
            if ($filterProvider->hasFilter($filterName)) {
                $argumentNames = $filterProvider->getFilterArgumentNames($filterName);
                // Remove the first argument, because the first argument is the value being filtered.
                array_shift($argumentNames);
                // Fill argument values
                $argumentNames = array_flip($argumentNames);

                $arguments = array_intersect_key($arguments, $argumentNames);

                return join(',', empty($arguments)? []: array_values($arguments));
            }
        }

    }

    private function getContainer(string $filterName): string
    {
        foreach($this->registry->all() as $filterProvider) {
            if ($filterProvider->hasFilter($filterName)) {
                return $filterProvider->getContainer();
            }
        }

        throw new MissingBladeFilterException(sprintf('Blade filter %s not exists', $filterName));
    }
}
