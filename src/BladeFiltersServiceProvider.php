<?php

namespace Pine\BladeFilters;

use Illuminate\Support\ServiceProvider;
use Pine\BladeFilters\FilterProvider\BladeFilterProviderRegistry;
use Illuminate\Support\Str;
use Pine\BladeFilters\FilterProvider\StaticMacroableFilterProvider;

class BladeFiltersServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(BladeFilterProviderRegistry::class, function ($app) {
            return new BladeFilterProviderRegistry();
        });

        $this->app->singleton('str.filter_provider', function ($app) {
            return new StaticMacroableFilterProvider(Str::class);
        });

        $this->app->singleton('blade_filter.filter_provider', function ($app) {
            return new StaticMacroableFilterProvider(BladeFilters::class);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $registry = $this->app[BladeFilterProviderRegistry::class];
        $registry
            ->register($this->app['blade_filter.filter_provider'])
            ->register($this->app['str.filter_provider'], 10);

        $this->app['blade.compiler']->extend(function ($view) {
            return $this->app[BladeFiltersCompiler::class]->compile($view);
        });

    }
}
