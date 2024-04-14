<?php

namespace mvnrsa\LiveCharts\Http\Livewire\Traits;

use DB;
use Str;
use Cache;
use Exception;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait LiveChart
{
	public $poll = 0;	// Default is no refresh
	public $uuid;		// Need a fixed, unique id for every chart for updates
	public $builder;	// Must be public to auto set from template unfortunately
	public $chartType;	// Type of chart to render
	public $colors;		// Color pallette
	public $is3D = false;
	public $pieHole = 0;

	public $library;	// Chart library to use google/chartjs
	public $labels = [];// Labels for chartjs charts

	private $query;
	private $bindings;
	private $connection;

	// This method must be overwritten in the component
	private function getData()
	{
		throw new Exception(class_basename($this) . "->getData() must be overwitten to return the correct data!");
	}

	// Initialise the uuid, poll interval and data
	public function mount()
	{
		parent::mount();

		// Set uuid, chartId, chartType and printButtonText
		$this->uuid = str_replace("-","_",Str::uuid());	// uuid() alone throws exception - livewire type not supported
		$this->chartId = $this->uuid;
		$this->chartType = class_basename($this);
		$this->printButtonText = trans('Print');

		// Failsafe - in case a non numeric poll interval was passed from template
		if (!is_numeric($this->poll))
			$this->poll = 0;

		// Chart data
		if (method_exists($this,"getExternalData"))
		{
			$this->chartType = class_basename(get_parent_class($this));
			$this->chartData = $this->getExternalData();
		}
		elseif ( $this->builder instanceof QueryBuilder || $this->builder instanceof EloquentBuilder )
		{
			$this->cacheBuilder();
			$this->chartData = $this->getData();
		}
		else
			throw new Exception($this->chartType . "->builder must be a query builder!");

		// Chart colors
		if (is_array($this->colors))
		{
			$this->optionsArray['colors'] = $this->colors;
			$this->colours = null;	// remove it from round trips
		}

		// 3D chart
		if ($this->is3D === true)
			$this->optionsArray['is3D'] = true;

		// DonutChart
		if (class_basename($this) == 'DonutChart')
		{
			$this->chartType = 'PieChart';	// A Donut is actually a pie with a hole :-)
			if (is_numeric($this->pieHole) && $this->pieHole > 0 && $this->pieHole < 1)
				$this->optionsArray['pieHole'] = $this->pieHole;
			else
				$this->optionsArray['pieHole'] = 0.4;
		}

		// Chart library
		$this->library = strtolower($this->library ?? config('livecharts.default_library','google'));

		// Convert for ChartJS
		if ($this->library == 'chartjs')
			$this->convertDataForChartJs();
	}

	// Unfortunately we can not cache the builder, so we have to cache the query, bindings and connection instead
	private function cacheBuilder()
	{
		$this->query = $this->builder->toSql();
		$this->bindings = $this->builder->getBindings();
		$this->connection = $this->builder->getConnection()->getConfig()['driver'];

		$cached = [ 'query'=>$this->query, 'bindings'=>$this->bindings, 'connection'=>$this->connection ];
		Cache::put("builder-$this->uuid", $cached, 3600);

		$this->builder = null;	// remove it from round trips
	}

	// Run the actual DB query
	private function runQuery()
	{
		// Fetch data and column names from database
		$data = collect(DB::connection($this->connection)->select($this->query, $this->bindings));
		$keys = collect(array_keys( (array)$data->first() ));

		return [ $data, $keys ];
	}

	// Convert data for ChartJS library
	private function convertDataForChartJs()
	{
		$this->labels = [];

		// This is a bit ugly and it assumes numeric array keys
		unset($this->chartData[0]);
		foreach ($this->chartData as $key => $row)
		{
			$this->labels[] = $row[0];

			unset ($this->chartData[$key][0]);
		}
	}

	// Dispatch event and data for chart update
	public function updateChart()
	{
		if (method_exists($this,"getExternalData"))
		{
			// Get new data from external source
			$this->chartData = $this->getExternalData();

			// Convert for ChartJS
			if ($this->library == 'chartjs')
				$this->convertDataForChartJs();

			// Dispatch an event to update the chart
			$this->dispatch("update-chart-$this->uuid", $this->chartData);
		}
		elseif ($cached = Cache::get("builder-$this->uuid"))
		{
			// Get the cached query with it's bindings from the cache
			list($this->query, $this->bindings, $this->connection) = array_values($cached);

			// Fetch the new data from the database
			$this->chartData = $this->getData();

			// Convert for ChartJS
			if ($this->library == 'chartjs')
				$this->convertDataForChartJs();

			// Dispatch an event to update the chart
			$this->dispatch("update-chart-$this->uuid", $this->chartData);
		}

		// Prevent redrawing the component
		$this->skipRender();
	}

	// Render the component
    public function render()
    {
		$template = Str::kebab($this->chartType);

		if (view()->exists("livecharts::{$this->library}-$template"))
        	return view("livecharts::{$this->library}-$template");
		if (view()->exists("livecharts::{$this->library}-default-chart"))
        	return view("livecharts::{$this->library}-default-chart");
		elseif (view()->exists("livecharts::$template"))
        	return view("livecharts::$template");

        return view("livecharts::default-chart");
    }
}
