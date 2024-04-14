<div style='margin: 2px solid black;'>
	    <script type="text/javascript">
			// Chart var, data and options
			var chart{{ $uuid }} = null;
			var chartData{{ $uuid }} = @json($chartData);
			var chartLabels{{ $uuid }} = @json($labels);
			var chartOptions{{ $uuid }} = @json($optionsArray);

			// Function that draws the chart	
	        function drawChart{{ $uuid }}() {
				console.log('draw chart{{ $uuid }}');
				console.log(chartData{{ $uuid }});
				console.log(chartLabels{{ $uuid }});
	        }

			drawChart{{ $uuid }}();

			@if ($poll > 0)
				// callback that redraws the chart
		        function redrawChart{{ $uuid }}(newData) {
					console.log('redraw chart{{ $uuid }}');
					// chart{{ $uuid }}.data.dataseries = newData;
		            // chart{{ $uuid }}.update();
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
</div>
