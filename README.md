Laravel Blade Filters
======================

![Laravel](https://img.shields.io/badge/Laravel-6--9-reds?style=flat&logo=laravel)
![php](https://img.shields.io/badge/php-%5E7.2.5%7C%5E8.0-green?style=flat&logo=php)
[![issues](https://img.shields.io/github/issues/videni/blade-filters)](https://github.com/videni/blade-filters/issues)
[![stars](https://img.shields.io/github/stars/videni/blade-filters)](https://github.com/videni/blade-filters/stargazers)
[![downloads](https://img.shields.io/packagist/dt/videni/blade-filters?style=plastic&style=flat&logo=Packagist)](https://packagist.org/packages/videni/blade-filters)

- [Laravel Blade Filters](#laravel-blade-filters)
  - [Installation](#installation)
  - [Named filter arguments](#named-filter-arguments)
  - [Pass variables to filter arguments](#pass-variables-to-filter-arguments)
  - [Add simple custom filter](#add-simple-custom-filter)
  - [Filter provider](#filter-provider)
  - [Internal filters](#internal-filters)
  - [Testing](#testing)

Originated from [`conedevelopment/blade-filters`](https://github.com/conedevelopment/blade-filters), but with lots of improvements, the original doesn't support named arguments and filter context, which are essential in my case. this library implements a lexer and parser to analyze filter syntax. 

Because this library is almost refactored and rewritten, this package renamed as `videni/blade-filters`, but the namespace still keeps it is.

## Installation

```
composer require "videni/blade-filters": "^1.0"
```

## Named filter arguments

```
{{ 'a wonderful place' | slug:separator='_', language='en' }}
```

For slug filter which provided by `\Illuminate\Support\Str`, the first argument is the value being filtered, the second argument would be the `separator`, the third would be `language`, if a argument name doesn't not exists in the slug method, it will be simply ignored.


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

A filter named `cdn_url` which generates url for an asset. 
```php
cdn_url('assets/carousel.css');
```
the domain of the CDN will change depending on the context where the filter run, the context itself is not part of the API of our filter, which the user doesn't need to worry about. you can always pass a variable to your filter as an argument following [Pass variables to filter arguments](#pass-variables-to-filter-arguments), however, the variable must be filled by the filter's user(you or someone), this is the difference between `filter context` and `filter argument`. 

filter context is a string which could be a full qualified class name or a variable in Blade view, it must have method access operator( ->, :: ) suffix, an example could be the  `getFilterContext` method of class `\Pine\BladeFilters\FilterProvider\StaticMacroableFilterProvider`.

```
    public function getFilterContext(): string
    {
        return sprintf('%s::', $this->class);
    }
```
## Internal filters

all static methods from `Pine\BladeFilters\BladeFilters` and `\Illuminate\Support\Str` are provided as blade filters, it is quite simple, please check its source code for reference.


## Testing

```
composer install 
./vendor/bin/phpunit
```
