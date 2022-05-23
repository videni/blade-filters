# Blade Filters

Forked from [`conedevelopment/blade-filters`](https://github.com/conedevelopment/blade-filters), comparing with the original, your can resolve filters with custom resolver.


```
    $assetContextResolver = function(){
            return '(isset($local_asset_context)? $local_asset_context: $base_asset_context)->';
    };
    BladeFiltersCompiler::extend('theme_asset_url', $assetContextResolver);
    BladeFiltersCompiler::extend('theme_asset_import', $assetContextResolver);
```


Please check original repo for other documents.