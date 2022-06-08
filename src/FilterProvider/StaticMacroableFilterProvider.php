<?php

namespace Pine\BladeFilters\FilterProvider;

use Pine\BladeFilters\Exceptions\MissingBladeFilterException;

class StaticMacroableFilterProvider implements FilterProviderInterface
{
    private string $class;

    public function __construct($class)
    {
        if (!class_exists($class)) {
            throw new \Exception(sprintf('class %s not exits', $class));
        }

        $this->class = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function hasFilter(string $filterName): bool
    {
        return method_exists($this->class, $filterName) ||
            (method_exists($this->class, 'hasMacro') && $this->class::hasMacro($filterName));
    }

    /**
     * {@inheritDoc}
     */
    public function processFilterArguments(string $filterName, array $filterArguments): string
    {
        $argumentNames = $this->getFilterArgumentNames($filterName);

        // Remove the first argument, because the first argument is the value being filtered.
        array_shift($argumentNames);
        // Fill argument values
        $argumentNames = array_flip($argumentNames);

        $arguments = array_intersect_key($filterArguments, $argumentNames);

        return join(',', empty($arguments)? []: array_values($arguments));
    }

 
    /**
     * {@inheritDoc}
     */
    public function getFilterContext(): string
    {
        return sprintf('%s::', $this->class);
    }

    /**
     * Get filter argument names sorted in order
     * Only used in blade compile phrase
     *
     * @param string $filterName
     *
     * @return array
     */
    protected function getFilterArgumentNames(string $filterName): array
    {
        $ref = new \ReflectionClass($this->class);
        $method  = null;

        if (method_exists($this->class, $filterName)) {
            $method = $ref->getMethod($filterName);
        } else if (method_exists($this->class, 'hasMacro') && $this->class::hasMacro($filterName)) {
            $micros = $ref->getStaticProperties();
            $method = new \ReflectionFunction($micros['macros'][$filterName]);
        } else {
            throw new MissingBladeFilterException(sprintf('Blade filter %s not exists in class %s', $filterName, $this->class));
        }

        return array_map(function($param) {
            return $param->name;
        }, $method->getParameters());
    }
}
