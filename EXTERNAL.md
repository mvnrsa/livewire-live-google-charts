## External Data Sources
To use an external data source, such as a third party API, just extend one of the chart components and add
a `getExternalData()` method to your component.

```
use mvnrsa\LiveCharts\Http\Livewire\LiveCharts\PieChart;

class MyExternalChart extends PieChart
{
    public function getExternalData()
    {
        $chartData = [];    // two dimensional array

        // do whatever it takes ...

        return $chartData; 
    }
}
```

The `getExternalData()` method has to do whatevever it needs to do the get the data and then return a two dimensional
array with the data.

### Blade
```
    @livewire('my-external-chart', $chartOptions)
```

### Data Structure
For a Pie or Donut chart each element in the array must have two elements, a string label and a numeric value:
```
    $data = [
               [ 'Category A', 1 ],
               [ 'Category B', 2 ],
               [ 'Category C', 3 ],
            ];
```

For all other chart types the first element of the array must contain the labels followed by the actaul data:
```
    $data = [
                [ 'X Label', 'Series 1', 'Series 2', /* ... */ ],
                [ 'One',   1, 1, /* ... */ ],
                [ 'Two',   2, 2, /* ... */ ],
                [ 'Three', 3, 3, /* ... */ ],
                [ 'Four',  4, 4, /* ... */ ],
            ];
```
