# Live Charts for Laravel Livewire 3

## Live?

**Live** as in the charts will **auto refresh** at a specified poll interval using the **Livewire wire:poll** attribute.

This package now supports both the **Google** and **ChartJS** charting libraries, or both on the same page.

Note that the component is only drawn the first time and thereafter only the data is updated on every poll, so the data transferred for polling is significantly less and the chart is just **updated**, not recreated every time.

### Coffee? ☕

One of my favorite escapes from coding and business is taking my wife for a **coffee**.  
(Which is pretty cheap in sunny South Africa).

If you use this package please think about how much time and effort you have saved and
**<a href='https://www.buymeacoffee.com/mvnrsa' target='_blank'>buy us a coffee</a>**.  ☕

## Requirements

- Laravel 9+
- Livewire 3+

## Installation
```
composer require mvnrsa/livewire-live-google-charts
```

## Configuration
A number of defaults can be set if you publish the config file:
```
php artisan vendor:publish --tag=livecharts
```
The values in the config file should be pretty self-explanotory.

## Obtaining the Data
The package uses a cached query builder (or any external data source) to fetch the data.
Actually only the query, bindings and connection is cached because we can not cache the builder class(es)
between requests.

You have to start by prepairing a builder that will fetch your data every time the data needs to be refreshed.

### Something like this:
```
$builder = Model::select('column',DB::raw("count(*) as cnt"))
                 ->groupBy('column')
                 ->orderBy('column');
```

### This builder will give you nice random data for testing:
```
$builder = Model::select( 'column',
                            DB::raw('FLOOR(1+rand()*10) AS `cnt 1`'),
                            DB::raw('FLOOR(1+rand()*10) AS `cnt 2`'),
                            // DB::raw('FLOOR(1+rand()*10) AS `cnt 3`'),
                            // etc.
                          )
                    ->groupBy('column')
                    ->orderBy('column');
```

### External Data Sources:
To use an external data source, such as a third party API, just extend one of the chart components and add
a `getExternalData()` method to your component.

See [EXTERNAL](EXTERNAL.md) for more details.

## Configure the Chart
```
$chartOptions = [ 'library'=>'chartjs', 'title'=>'Chart Title', 'builder'=>$builder,
                  'poll'=>2, 'width'=>500, height=>250, /* ... */ ];
```
- library selects which charting library to use - google/chartjs
- title should be obvious - leave empty for no title inside the chart
- builder is the builder instance (without `->get()`!)
- poll is the poll interval in seconds - **0 means no polling/refresh** it will just draw the chart once
- width and height can be anything that HTML will understand - px, %, em, etc.
- colors provide a color pallette as an array of colors
- is3D (true/false) make some charts 3D
- pieHole (0.0 to 1.0) controls the relative size of the pie hole for donut charts

Most of the options have sensible defaults from the config file and can be left out.

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
- Candlestick (Google only)

Just replace pie in the blade example above with donut, bar, column, etc.

Note that for a candlestick chart the builder should return 5 columns.

## Colors
You can specify the color pallete for the chart by adding a `colors` array to the options.  
Any color that will work in HTML will work eg:
```
$colors = [ 'red,'#00ff00','#0000ff','pink','cyan' ];
$chartOptions = [ 'title'=>'Chart Title', 'builder'=>$builder, 'poll'=>2, 'colors'=>$colors ];
```

## Other Library Options
Any other options can be passed to the chart library by simply adding an `options` array to the chart options:
```
$chartOptions = [ .... 'options'=> [ /* other options here */ ] ];

```

### Author

[Marnus van Niekerk](https://github.com/mvnrsa) - [mvnrsa](https://github.com/mvnrsa) - [laravel@mjvn.net](mailto:laravel@mjvn.net)


### Credit to Helvetitec
This package was originally an extension of the excellent
[Helvetitec/lagoon-charts](https://github.com/Helvetitec/lagoon-charts)
Google charts package by [Helvetitec](https://github.com/Helvetitec).  
Except these ones are "live" :-)

