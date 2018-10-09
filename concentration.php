<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 9/05/2018
 * Time: 4:33 PM
 */

use phpformbuilder\database\Mysql;

$filter = $form->getFilterArray();
$db->selectRows("concentration_curve", $filter);

$XY_data = array();
$X = array();
$Y = array();
if (!$db->error()) {
    $curve_data = $db->recordsArray();
    foreach ($curve_data as $point) {
        $X[] = $point["tally_count"];
        $Y[] = $point["unique_spec"];
        $XY_data[] = '[' . $point["tally_count"] . ', ' . $point["unique_spec"] . ', null]';
    }
}
$XY_data_string = implode($XY_data,", ");

if ($db->rowCount() > 0) {
    // Create log trendline
    $logX = array_map('log', $X);
    $n = count($X);
    $square = function ($x) {return pow($x,2);};
    $multiply = function($x,$y) {return $x*$y;};
    $x_squared = array_sum(array_map($square, $logX));
    $xy = array_sum(array_map($multiply, $logX, $Y));

    // Log function of the form $aFit*log(x)+$bFit
    $bFit = ($n * $xy - array_sum($Y) * array_sum($logX)) /
        ($n * $x_squared - pow(array_sum($logX), 2));
    $aFit = (array_sum($Y) - $bFit * array_sum($logX)) / $n;

    // Create an array of of XY values that will represent the log trendline
    $num_points = end($X); // The x value of the last data point on the plot
    for ($i=1; $i<=$num_points; $i+=0.5) {
        $log_data[] = '[' . $i . ', null, ' . ($aFit*log($i)+$bFit) . ']';
    }
    $log_data_string = implode($log_data,", ");

    echo "
        <script type=\"text/javascript\" src=\"https://www.gstatic.com/charts/loader.js\"></script>
        <script>
        google.charts.load('current', {packages: ['corechart']});
        google.charts.setOnLoadCallback(drawBasic_concentration);
        
        function drawBasic_concentration() {
        
            var data = new google.visualization.DataTable();
            data.addColumn('number','x');
            data.addColumn('number','y');
            data.addColumn('number','trendline');
            data.addRows([$XY_data_string]);
            data.addRows([$log_data_string]);

            
            var options = {                
                title:'Species Accumulation Curve',
                seriesType:'scatter',
                series: {
                    1: {
                        type: 'line'
                    }
                },
                colors: ['#4286f4','#9bc1ff'],
                chartArea: {wight:350},
                hAxis: {
                  title: 'Tally Count'
                },
                vAxis: {
                  title: 'Unique Specimens'
                },
                legend: 'none'
            };
            
            var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
            
            chart.draw(data, options);
            
            var ref = $(\"button[name='stats-btn']\");
            var popup = $(\"#chart_div\");
            var popper = new Popper(ref, popup, {
                placement: 'bottom'
            });
    
            ref.click(function() {
                popup.toggle();
                popper.scheduleUpdate();
            });
        }
        </script>";
}