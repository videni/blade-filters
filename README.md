Laravel Blade Filters
======================

- [Laravel Blade Filters](#laravel-blade-filters)
  - [Installation](#installation)
  - [Named filter arguments](#named-filter-arguments)
  - [Pass variables to filter arguments](#pass-variables-to-filter-arguments)
  - [Add simple custom filter](#add-simple-custom-filter)
  - [Filter provider](#filter-provider)
  - [Internal filters](#internal-filters)
  - [Testing](#testing)

Originated from [`conedevelopment/blade-filters`](https://github.com/conedevelopment/blade-filters), but with huge improvements, the original doesn't support named arguments and filter context, which are essential in my case. this library implements a custom lexer and parser to analyze filter syntax. 

Because this library is almost refactored, this package renamed as `videni/blade-filters`, but the namespace still keeps it is.

## Installation

```
composer require "videni/blade-filters": "^1.0"
```

## Named filter arguments

```
{{ 'a wonderful place' | slug:separator='_', language='en' }}
```

For slug filter which provided by `\Illuminate\Support\Str`, the first argument is the value being filtered, the second argument would be the `separator`, the third would be `language`, if a argument name doesn't not exists in slug method of `\Illuminate\Support\Str`, it will be simply ignored.


## Pass variables to filter arguments

```
{{ "hello world" | slug:separator=$separator }}
```

the `$separator` will be captured where the filter runs.

## Add simple custom filter

For the simplest case, you can add custom filter  as following
```
  \Pine\BladeFilters\BladeFilters::macro('script_tag', function (string $asset,$type = 'text/javascript', $async = null, $defer = null) {
      // Your code here
    }
)
```

## Filter provider

You may not need this if you just want to add [simple custom filters](#add-simple-custom-filter). 

The provided `StaticMacroableFilterProvider` class allows you to hook static methods and `Laravel Macroable` as Blade filters. usually, you don't need to add a `static macroable` class like  `\Illuminate\Support\Str` and `\Pine\BladeFilters\BladeFilters`, you can use `StaticMacroableFilterProvider` directly, if you want to support other third party utilities class. for example,

```
$registry = new BladeFilterProviderRegistry();
$registry
    ->register(new StaticMacroableFilterProvider(\Illuminate\Support\Str::class), 10);
```

Uncommonly, your filter may be context aware, let's assume a context like this:

A filter named `cdn_url` which generated url for an asset. 
```php
cdn_url('assets/carousel.css');
```
the domain of the CDN will change depending on the context where the filter run, the context itself is not part of the API of our filter, which the user doesn't need to worry about. you can always pass a variable to your filter as an argument following [Pass variables to filter arguments](#pass-variables-to-filter-arguments), however, the variable must be filled by the filter's user(you or someone), this is the difference between `filter context` and `filter argument`. 

## Internal filters

all static methods from `Pine\BladeFilters\BladeFilters` and `\Illuminate\Support\Str` are provided as blade filters, it is quite simple, please check its source code reference.


## Testing

```
phpunit
```
