<div>
    <div id="{{ $uuid }}" @if($poll > 0) wire:poll.{{ $poll }}s="updateChart" @endif
			style="height:{{ $height }}; width:{{ $width }};">
		<canvas id="canvas{{ $uuid }}"></canvas>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script type="text/javascript">
		// Chart var, data and options
		var chart{{ $uuid }} = null;
		var canvas{{ $uuid }} = null;
		var chartData{{ $uuid }} = @json((array)$chartData);
		var chartLabels{{ $uuid }} = @json($labels);
		var chartOptions{{ $uuid }} = @json($optionsArray);

		// Function that draws the chart	
        function drawChart{{ $uuid }}() {
			canvas{{ $uuid }} = document.getElementById("canvas{{ $uuid }}");

			chart{{ $uuid }} = new Chart(canvas{{ $uuid }}, {
				type: '{{ $jsType }}',
				data: {
					labels: chartLabels{{ $uuid }},
					datasets: chartData{{ $uuid }},
				},
				options: {
					scales: {
						y: {
							beginAtZero: true
						}
					}
				}
			});
        }

		drawChart{{ $uuid }}();

		@if ($poll > 0)
			// callback that redraws the chart
	        function redrawChart{{ $uuid }}(newData) {
				console.log('redraw chart{{ $uuid }}');
				chart{{ $uuid }}.data.datasets = newData;
	            chart{{ $uuid }}.update('none');
	        }

			// event listener for updates
			document.addEventListener('livewire:init', () => {
				Livewire.on('update-chart-{{ $uuid }}', (event) => {
					redrawChart{{ $uuid }}(event[0]);
				});
			});
		@endif

    </script>
</div>
