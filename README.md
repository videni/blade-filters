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
  - [Using the filters](#using-the-filters)
      - [Regular usage:](#regular-usage)
      - [Chained usage:](#chained-usage)
      - [Passing non-static values:](#passing-non-static-values)
      - [Passing variables as filter parameters:](#passing-variables-as-filter-parameters)
      - [Built-in Laravel functionality:](#built-in-laravel-functionality)
  - [The Filters](#the-filters)
    - [About the filters](#about-the-filters)
    - [The available filters](#the-available-filters)
      - [Currency](#currency)
      - [Date](#date)
      - [Lcfirst](#lcfirst)
      - [Reverse](#reverse)
      - [Substr](#substr)
      - [Trim](#trim)
      - [Ucfirst](#ucfirst)
    - [Supported built-in Str functions](#supported-built-in-str-functions)
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
## Using the filters

You can use the filters in any of your blade templates. 

#### Regular usage:

```php
{{ 'john' | ucfirst }} // John
```

#### Chained usage:

```php
{{ 'john' | ucfirst | substr:start=0,length=1 }} // J
{{ '1999-12-31' | date:format='Y/m/d' }} // 1999/12/31
```

#### Passing non-static values:

```php
{{ $name | ucfirst | substr:start=0,length=1 }}
{{ $user['name'] | ucfirst | substr:start=0,length=1 }}
{{ $currentUser->name | ucfirst | substr:start=0,length=1 }}
{{ getName() | ucfirst | substr:start=0,length=1 }}
```

#### Passing variables as filter parameters:

```php
$currency = 'HUF'
{{ '12.75' | currency:currency=$currency }} // HUF 12.75
```

#### Built-in Laravel functionality:

```php
{{ 'This is a title' | slug }} // this-is-a-title
{{ 'This is a title' | title }} // This Is A Title
{{ 'foo_bar' | studly }} // FooBar
```


## The Filters

### About the filters

All static methods from `Pine\BladeFilters\BladeFilters` and `\Illuminate\Support\Str` are provided as blade filters, it is quite simple, you can also check its source code for reference.

### The available filters

The package comes with a few built-in filters, also the default Laravel string methods can be used.

#### Currency

```php
{{ '17.99' | currency:currency='CHF' }} // CHF 17.99
{{ '17.99' | currency:currency='€',left=false }} // 17.99 €
```

> Passing `false` as the second parameter will align the symbol to the right.
#### Date

```php
{{ '1999/12/31' | date }} // 1999-12-31
{{ '1999/12/31' | date:format='F j, Y' }} // December 31, 1999
```

#### Lcfirst

```php
{{ 'Árpamaláta' | lcfirst }} // árpamaláta
```

> Unlike PHP's default `lcfirst()`, this filter works with multi-byte strings as well.
#### Reverse

```php
{{ 'ABCDEF' | reverse }} //FEDCBA
```

#### Substr

```php
{{ 'My name is' | substr:start=0,length=2 }} // My
{{ 'My name is' | substr:start=3 }} // name is
```

#### Trim

```php
{{ '   trim me    ' | trim }} // trim me
```

#### Ucfirst

```php
{{ 'árpamaláta' | ucfirst }} // Árpamaláta
```

> Unlike PHP's default `ucfirst()`, this filter works with multi-byte strings as well.
### Supported built-in Str functions

- [Str::after()](https://laravel.com/docs/5.8/helpers#method-str-after)
- [Str::before()](https://laravel.com/docs/5.8/helpers#method-str-before)
- [Str::camel()](https://laravel.com/docs/5.8/helpers#method-str-camel)
- [Str::finish()](https://laravel.com/docs/5.8/helpers#method-str-finish)
- [Str::kebab()](https://laravel.com/docs/5.8/helpers#method-str-kebab)
- [Str::limit()](https://laravel.com/docs/5.8/helpers#method-str-limit)
- [Str::plural()](https://laravel.com/docs/5.8/helpers#method-str-plural)
- [Str::singular()](https://laravel.com/docs/5.8/helpers#method-str-singular)
- [Str::slug()](https://laravel.com/docs/5.8/helpers#method-str-slug)
- [Str::snake()](https://laravel.com/docs/5.8/helpers#method-str-snake)
- [Str::start()](https://laravel.com/docs/5.8/helpers#method-str-start)
- [Str::studly()](https://laravel.com/docs/5.8/helpers#method-str-studly)
- [Str::title()](https://laravel.com/docs/5.8/helpers#method-str-title)

## Testing

```
composer install 
./vendor/bin/phpunit
```
