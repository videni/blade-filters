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
        if (! preg_match('/(?=(?:[^\'\"\`)]*([\'\"\`])[^\'\"\`]*\1)*[^\'\"\`)]*$)(\|.*)/u', $value, $matches)) {
            return $value;
        }

        $filterExpression = $this->parser->parse($value);
        if (empty($filterExpression['filters'])) {
            return $value;
        }

        $prefiltered = $filterExpression['prefiltered'];
        $filters = $filterExpression['filters'];

        $wrapped = '';
        $first = array_shift($filters);

        $wrapped = sprintf(
            $this->getFilterContext($first['name']).'%s(%s,%s)',
            $first['name'],
            $prefiltered,
            $this->stringifyArguments($first['name'], $first['arguments'])
        );

        foreach ($filters as $filter) {
            $filterName = $filter['name'];
            $arguments = $filter['arguments'];

            $wrapped = sprintf(
                $this->getFilterContext($filterName).'%s(%s,%s)',
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
                return $filterProvider->processFilterArguments($filterName, $arguments);
            }
        }

        throw new MissingBladeFilterException(sprintf('Blade filter %s not exists', $filterName));
    }

    private function getFilterContext(string $filterName): string
    {
        foreach($this->registry->all() as $filterProvider) {
            if ($filterProvider->hasFilter($filterName)) {
                return $filterProvider->getFilterContext();
            }
        }

        throw new MissingBladeFilterException(sprintf('Blade filter %s not exists', $filterName));
    }
}
