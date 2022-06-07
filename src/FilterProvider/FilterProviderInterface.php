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
     * Get filter argument names sorted in order
     * Only used in blade compile phrase
     *
     * @param string $filterName
     *
     * @return array
     */
    public function getFilterArgumentNames(string $filterName): array;

    /**
     * The stringified container for the filter to run
     *
     * @return string
     */
    public function getContainer(): string;
}
