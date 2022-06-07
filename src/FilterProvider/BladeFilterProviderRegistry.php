<?php

namespace Pine\BladeFilters\FilterProvider;

class BladeFilterProviderRegistry
{
    private $providers = [];

    public function all(): iterable
    {
        if ($this->sorted === false) {
            /** @psalm-suppress InvalidPassByReference Doing PHP magic, it works this way */
            array_multisort(
                array_column($this->providers, 'priority'),
                \SORT_DESC,
                array_keys($this->providers),
                \SORT_ASC,
                $this->providers
            );

            $this->sorted = true;
        }

        foreach ($this->providers as $record) {
            yield $record['provider'];
        }
    }

    public function register(FilterProviderInterface $provider, int $priority = 0): self
    {
        $this->providers[] = ['provider' => $provider, 'priority' => $priority];
        $this->sorted = false;

        return $this;
    }

    public function unregister($provider): self
    {
        if (!$this->has($provider)) {
            throw new \LogicException(
                sprintf('Filter provider %s not exists', get_class($provider),
                array_map('get_class', array_column($this->providers, 'provider')))
            );
        }

        $this->providers = array_filter(
            $this->providers,
            static function (array $record) use ($provider): bool {
                return $record['provider'] !== $provider;
            }
        );

        return $this;
    }

    public function has($provider): bool
    {
        foreach ($this->providers as $record) {
            if ($record['provider'] === $provider) {
                return true;
            }
        }

        return false;
    }
}
