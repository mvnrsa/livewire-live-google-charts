<?php

namespace mvnrsa\LiveCharts\Http\Livewire\LiveCharts;

use Str;
use Exception;
use Livewire\Component;
use mvnrsa\LiveCharts\Http\Livewire\Traits\LiveChart;
// use Helvetitec\LagoonCharts\Http\Livewire\BarChart as ParentChart;

class BarChart extends Component // ParentChart
{
	use LiveChart;

	private function getData()
	{
		list($data, $keys) = $this->runQuery();

		if ($data->count() < 1)
			throw new Exception(class_basename($this) . "->builder must return rows with at least one column");

		// Beautify the labels
		foreach ($keys as $ids => $key)
			$keys[$ids] = Str::title(strtolower($key));

		// Transform to an array that the chart library understands
		$newData = $data->prepend($keys->values()->toArray())
						->transform(function ($row) {
										return array_values((array)$row);
									})
						->toArray();

		return $newData;
	}
}
