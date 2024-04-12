<div>
	<div>
	    {{-- Add @lagoonScripts('en') --}}
	
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
		
		            @foreach($actions as $action)
		            {!! $action !!}
		            @endforeach
		
		            @foreach($events as $event)
		            {!! $event !!}
		            @endforeach
	
		            chart{{ $uuid }}.draw(data, chartOptions{{ $uuid }});
		
		            @if($printable)

		                document.getElementById('lagoon-printable-{{ $uuid }}').outerHTML = '<div style="display: flex; justify-content: center;"><a href="' + chart{{ $uuid }}.getImageURI() + '" target="_blank">{{ $printButtonText }}</a></div>';

		            @endif
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
		
	    <div id="{{ $uuid }}" @if($poll > 0) wire:poll.{{ $poll }}s="updateChart" @endif style="height:100%; width:100%;">
		</div>
	    @if ($printable)
	        <div id="lagoon-printable-{{ $uuid }}">
				{{ $printButtonText }}
			</div>
	    @endif
	</div>
</div>
