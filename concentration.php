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

$result = array();
if (!$db->error()) {
    $curve_data = $db->recordsArray();
    $result[] = '["Tally Count", "Data Point"]';
    foreach ($curve_data as $point) {
        $result[] = '[' . $point["tally_count"] . ', ' . $point["unique_spec"] . ']';

    }
}

$result_concentration = implode($result,", ");
if ($db->rowCount() > 0) {
    echo "
        <script type=\"text/javascript\" src=\"https://www.gstatic.com/charts/loader.js\"></script>
        <script>
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawBasic_concentration);
        
        function drawBasic_concentration() {
        
            var data = google.visualization.arrayToDataTable([
                $result_concentration
            ]);
            
            var options = {
                title:'Concentration Curve',
                chartArea: {width:300},
                hAxis: {
                  title: 'Tally Count'
                },
                vAxis: {
                  title: 'Unique Specimens'
                },
                legend: 'none',
                trendlines: {
                  0: {
                    type: 'exponential',
                    visibleInLegend: true,
                  }
                }
            };
            
            var chart = new google.visualization.ScatterChart(document.getElementById('chart_div'));
            
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