<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 9/05/2018
 * Time: 4:33 PM
 */

require_once 'classes/Page_Renderer.php';
require_once 'Chart.js-PHP-master/src/ChartJS.php';

use phpformbuilder\database\Mysql;
use ChartJs\ChartJS;

$db = new Mysql();

$db->selectRows("concentration_curve", array('sample_id' => Mysql::SQLValue('S1'), 'core_id' => Mysql::SQLValue('C1'), 'project_id' => Mysql::SQLValue('PROJECT_1')));

$curve_data = array();
if (!$db->error()) {
    $table_data = $db->recordsArray();
    foreach ($table_data as $point) {
        $curve_data[] = ['x' => $point["tally_count"], 'y' => $point["unique_spec"]];
    }
} else {
    $curve_data = array();
}

?>
<html>
<body>
<?php


?>
<html>
<body>
<?php
echo $Line;
?>
<script src="js/Chart.min.js"></script>
<script src="js/driver.js"></script>
<script>
    (function() {
        loadChartJsPhp();
    })();
</script>
</body>
</html>