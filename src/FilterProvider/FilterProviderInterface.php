<?php

namespace Pine\BladeFilters\FilterProvider;

interface FilterProviderInterface
{
    /**
     * Whether a filter exists
     *
     * @param string $filterName
     * @return boolean
     */
    public function hasFilter(string $filterName): bool;

    /**
     * Process filter arguments
     *
     * @param string $filterName
     * @param array $filterArguments
     * @return string
     */
    public function processFilterArguments(string $filterName, array $filterArguments): string;

    /**
     * A string represents filter context where a filter to run, which 
     * could be a full qualified class name or a variable in Blade view, 
     * it must have method access operator(->, ::) suffix.
     *
     * @return string
     */
    public function getFilterContext(): string;
}
