<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-row">
                <div class="basis-1/2 grid grid-cols-4 gap-4 p-6 text-gray-900">


                    <div id="curve_chart"></div>

                </div>
                <div class="basis-1/2 grid grid-cols-4 gap-4 p-6 text-gray-900">


                    <div id="curve_chart1" style="width: auto;"></div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Date', 'Orders', ],
                ['25 July', 5, ],
                ['26 July', 50, ],
                ['27 July', 20, ],
                ['28 July', 15, ],
                ['29 July', 5, ],
                ['30 July', 100, ],
                ['31 July', 120, ]
            ]);

            var options = {
                title: 'Orders',
                curveType: 'function',
                legend: {
                    position: 'bottom'
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
            chart.draw(data, options);
        }
    </script>

    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Date', 'Amount', ],
                ['25 July', 1000, ],
                ['26 July', 1500, ],
                ['27 July', 7000, ],
                ['28 July', 4000, ],
                ['29 July', 8000, ],
                ['30 July', 3000, ],
                ['31 July', 6000, ]
            ]);

            var options = {
                title: 'Amount',
                curveType: 'function',
                legend: {
                    position: 'bottom'
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart1'));
            chart.draw(data, options);
        }
    </script>
</x-app-layout>