<?php

namespace mvnrsa\LiveCharts\Http\Livewire\LiveCharts;

use Str;
use Exception;
use Livewire\Component;
use mvnrsa\LiveCharts\Http\Livewire\Traits\LiveChart;
use Helvetitec\LagoonCharts\Http\Livewire\CandlestickChart as ParentChart;

class CandlestickChart extends ParentChart
{
	use LiveChart;

	private function getData()
	{
		list($data, $keys) = $this->runQuery();

		if ($data->count() < 1 || $keys->count() < 3 || $keys->count() > 5)
			throw new Exception(class_basename($this) . "->builder must return rows with exactly 3 or 5 columns");

		// Beautify the keys
		foreach ($keys as $ids => $key)
			$keys[$ids] = Str::title(strtolower($key));

		// Transform to an array that the chart library understands
		$newData = $data->prepend($keys->values()->toArray())
						->transform(function ($row) {
										$row = array_values((array)$row);

										// Add 4th and 5th column if needed
										if (count($row) < 4)
											$row[3] = $row[1];
										if (count($row) < 5)
											$row[4] = $row[2];

										return $row;
									})
						->toArray();

		return $newData;
	}
}
