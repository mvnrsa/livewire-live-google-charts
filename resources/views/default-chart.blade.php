<div>
	@once
		<style>
			svg > g > g.google-visualization-tooltip { pointer-events: none }
		</style>

		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">
			google.charts.load( 'current', { 'packages':[ 'corechart' ], 'language': '{{ app()->getLocale() }}' } );
		</script>';
	@endonce

	<div>
	    <script type="text/javascript">
			// Chart var, data and options
			var chart{{ $uuid }} = null;
			var chartData{{ $uuid }} = @json($chartData);
			var chartOptions{{ $uuid }} = @json($optionsArray);

	        google.charts.setOnLoadCallback(drawChart{{ $uuid }});
	
	        // Callback that creates and populates a data table,
	        // instantiates the pie chart, passes in the data and
	        // draws it.
	        function drawChart{{ $uuid }}() {

	            // Create the data table.
				@if (!empty($column1) && !empty($column2))

	            	var data = new google.visualization.DataTable();
		            data.addColumn('string', '{{ $column1 }}');
		            data.addColumn('number', '{{ $column2 }}');
	            	data.addRows(chartData{{ $uuid }});

				@else

					var data = google.visualization.arrayToDataTable(chartData{{ $uuid }});

				@endif
	
	            // Instantiate and draw our chart, passing in some options.
	            chart{{ $uuid }} = new google.visualization.{{ $chartType }}(document.getElementById('{{ $uuid }}'));
	
	            chart{{ $uuid }}.draw(data, chartOptions{{ $uuid }});
	        }

			@if ($poll > 0)
				// callback that redraws the chart
		        function redrawChart{{ $uuid }}(newData) {

		            // Create the data table.
					@if (!empty($column1) && !empty($column2))

			            var data = new google.visualization.DataTable();
			            data.addColumn('string', '{{ $column1 }}');
			            data.addColumn('number', '{{ $column2 }}');
		    	        data.addRows(newData);

					@else

						var data = google.visualization.arrayToDataTable(newData);

					@endif
		
		            chart{{ $uuid }}.draw(data, chartOptions{{ $uuid }});
		        }
	
				// event listener for updates
				document.addEventListener('livewire:init', () => {
					Livewire.on('update-chart-{{ $uuid }}', (event) => {
						redrawChart{{ $uuid }}(event[0]);
					});
				});
			@endif

	    </script>
		
	    <div id="{{ $uuid }}"
					@if($poll > 0) wire:poll.{{ $poll }}s="updateChart" @endif
					style="@if(!empty($height)) height:{{ $height }}{{ is_numeric($height) ? 'px' : '' }}; @endif @if(!empty($width)) width:{{ $width }}{{ is_numeric($width) ? 'px' : '' }}; @endif">
		</div>
	</div>
</div>
