<?php

namespace mvnrsa\LiveCharts\Http\Livewire\LiveCharts;

use Str;
use Exception;
use Livewire\Component;
use mvnrsa\LiveCharts\Http\Livewire\Traits\LiveChart;
// use Helvetitec\LagoonCharts\Http\Livewire\PieChart as ParentChart;

class PieChart extends Component // ParentChart
{
	use LiveChart;

	private function getData()
	{
		list($data, $keys) = $this->runQuery();
		if ($data->count() < 1
				|| $keys->count() != 2 
				|| !is_string( $data->first()->{$keys[0]} )
				|| !is_numeric( $data->first()->{$keys[1]} )
			)
			throw new Exception(class_basename($this) . "->builder must return rows with two columns: string, number!");

		// Set the column names
		$this->column1 = $keys[0];
		$this->column2 = $keys[1];

		// Transform to an array that the chart library understands
		$newData = $data->transform(function ($row) use ($keys) {
										return [ Str::title(strtolower($row->{$keys[0]})), $row->{$keys[1]} ];
									})
						->toArray();

		return $newData;
	}
}
