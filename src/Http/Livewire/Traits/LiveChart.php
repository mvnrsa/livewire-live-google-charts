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
	public $poll;		// Default is no refresh
	public $uuid;		// Need a fixed, unique id for every chart for updates
	public $builder;	// Must be public to auto set from template unfortunately
	public $chartType;	// Type of chart to render
	public $colors;		// Color pallette
	public $is3D = false;
	public $pieHole = 0;

	public $library;	// Chart library to use google/chartjs
	public $labels = [];// Labels for chartjs charts
	public $jsType;		// ChartJS type

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

		// Failsafe - in case no or non numeric poll interval was passed from template
		if (!is_numeric($this->poll))
			$this->poll = config('livecharts.default_poll',2);

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
			$this->optionsArray['colors'] = $this->colors;
		elseif (is_array($colors = config('livecharts.colors')))
			$this->optionsArray['colors'] = $colors;
		$this->colours = null;	// remove it from round trips

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

	// Convert data for ChartJS library
	// This is a bit ugly because it traverses the arrays and it assumes numeric array keys
	private function convertDataForChartJs()
	{
		if (empty($this->jsType))
		{
			$this->jsType = explode("-", Str::kebab($this->chartType))[0];	// First word of kebab (lowercase) classname

			if ($this->jsType == 'column')
				$this->jsType = 'bar';
			elseif ($this->jsType == 'donut')
				$this->jsType = 'doughnut';
		}

		if (in_array($this->jsType,[ 'pie', 'doughnut' ]))
			return $this->convertPieDataForChartJs();
		
		$newData = [];
		$this->labels = [];

		// Initialise datasets & colors
		foreach ($this->chartData[0] as $key => $label)
			if ($key > 0)
			{
				$newData[$key-1] = [
									'label'=>$label,
									'borderWidth'=>$this->options['borderWidth'] ?? config('livecharts.borderWidth',1),
								   ];
				if (is_array($this->colors))
				{
					$newData[$key-1]['backgroundColor'] = $this->colors[$key%count($this->colors)];
					$newData[$key-1]['borderColor'] = $newData[$key-1]['backgroundColor'];
				}
			}

		// Convert actual data
		unset($this->chartData[0]);
		foreach ($this->chartData as $rowPos => $row)
		{
			$this->labels[] = $row[0];

			unset($this->chartData[$rowPos][0]);

			foreach ($row as $colPos=> $val)
				if ($colPos > 0)
					$newData[$colPos-1]['data'][$rowPos-1] = $val;
		}

		$this->chartData = $newData;
	}

	// Convert data dfor pie and donut charts
	// It's aa bit less ugly
	private function convertPieDataForChartJs()
	{
		$newData = [];
		$collection = collect($this->chartData);

		$this->labels = $collection->pluck(0)->toArray();

		// Dataset
		$newData = [ [ "label" => $this->title, "data" => $collection->pluck(1)->toArray() ] ];

		// Colors
		if (is_array($this->colors))
			foreach ($newData[0]["data"] as $pos => $value)
				$newData[0]['backgroundColor'][$pos] = $this->colors[$pos%count($this->colors)];

		$this->chartData = $newData;
	}
}
