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
     * The stringified container for the filter to run
     *
     * @return string
     */
    public function getContainer(): string;
}
