<?php

namespace mvnrsa\LiveCharts;

use Exception;
use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;

use mvnrsa\LiveCharts\Http\Livewire\LiveCharts\LineChart;
use mvnrsa\LiveCharts\Http\Livewire\LiveCharts\PieChart;
use mvnrsa\LiveCharts\Http\Livewire\LiveCharts\DonutChart;
use mvnrsa\LiveCharts\Http\Livewire\LiveCharts\AreaChart;
use mvnrsa\LiveCharts\Http\Livewire\LiveCharts\BarChart;
use mvnrsa\LiveCharts\Http\Livewire\LiveCharts\ColumnChart;
use mvnrsa\LiveCharts\Http\Livewire\LiveCharts\CandlestickChart;

class LiveChartsServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->mergeConfigFrom(__DIR__.'/../config/config.php', 'livecharts');
	}

	public function boot()
	{
		$this->loadViewsFrom(__DIR__.'/../resources/views', 'livecharts');

		Livewire::component('livecharts-line-chart', LineChart::class);
		Livewire::component('livecharts-pie-chart', PieChart::class);
		Livewire::component('livecharts-donut-chart', DonutChart::class);
		Livewire::component('livecharts-area-chart', AreaChart::class);
		Livewire::component('livecharts-bar-chart', BarChart::class);
		Livewire::component('livecharts-column-chart', ColumnChart::class);
		Livewire::component('livecharts-candlestick-chart', CandlestickChart::class);

		if ($this->app->runningInConsole())
		{
			$this->publishes([
				__DIR__.'/../config/config.php' => config_path('livecharts.php'),
			], 'config');
		}
	}
}
