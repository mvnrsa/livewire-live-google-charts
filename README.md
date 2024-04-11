# Live Google Charts for Laravel Livewire 3
(Auto refresh/poll charts)


## Live?
**Live** as in the charts will auto refresh at a specified poll interval using the **Livewire wire:poll** attribute

Note that the component is only drawn the first time and thereafter only the data is updated on every poll, so the data used for polling is siqnificantly less and the chart is just **updated, not recreated** every time.

## Credit to Helvetitec
This package is an extension of the excellent [Helvetitec/lagoon-charts](https://github.com/Helvetitec/lagoon-charts) Google charts package by [Helvetitec](https://github.com/Helvetitec).
(Except these ones are "live" :-)

If you are only looking for static charts, just use their package because this one requires it anyway.

## Requirements

- Laravel 9+
- Livewire 3+

## Installation
```
composer require mvnrsa/livewire-live-google-charts
```

## Usage
You have to add `@lagoonScripts` and `@lagoonStyles` from the lagoon-charts package to the layouts that will have charts on them.
```
@lagoonStyles <!-- This will add a small style part which will cause tooltipps stop cliping when hover over with the mouse -->
```
```
@lagoonScripts('en') <!-- The only parameter needed is the localization parameter, you can use any language recognized by Google -->
@lagoonScripts({{ app()->getLocale() }}) <!-- This will set the localization to the locale set in Laravel -->
```

## Obtaining the data
The package uses a cached query builder to query the database and fetch the data.  Actually only the query, bindings and connection is cached because we can not cache the builder class(es) between requests.

You have to start by prepairing a builder that will fetch your data every time the query is refreshed.  Something line this:
```
$builder = User::select('department',DB::raw("count(*) as cnt")->groupBy('department')->orderBy("department");

$chartOptions = [ 'title'=>'Chart Title', 'builder'=>$builder ];
```

### This builder will give you random data for testing:
```
$builder = User::->select('name',
                                    DB::raw('FLOOR(1+rand()*10) AS `cnt 1`'),DB::raw('FLOOR(1+rand()*10) AS `cnt 2`'),
                                    DB::raw('FLOOR(1+rand()*10) AS `cnt 3`'),DB::raw('FLOOR(1+rand()*10) AS `cnt 4`')
                            )
                    ->orderBy('name')
                    ->groupBy('name');
```

## Blade
```
@livewire('livecharts-pie-chart', $chartOptions)
```

## Available charts
- Pie
- Donut
- Bar
- Column
- Line
- Area
- Candlestick

Just replace pie in the blade example below with donut, bar, column, line, area or candlestick.

Note that for a candlestick chart the builder must ideally return 5 columns, but 3 will also work.

## Colors
You can specify the colout pallete for the chart by adding a `colors` array to the options.
Any color that will work in HTML will work eg:
```
$colors = ['red,'#00ff00','#0000ff','pink','cyan'];
$chartOptions = [ 'title'=>'Chart Title', 'builder'=>$builder, 'colors'=>$colors ];
```

## 3D Charts

Some charts can be made 3D by adding `'is3D'=>true` to the options.

### Author

[Marnus van Niekerk](https://github.com/mvnrsa) - [mvnrsa](https://github.com/mvnrsa) - [laravel@mjvn.net](mailto:laravel@mjvn.net)
