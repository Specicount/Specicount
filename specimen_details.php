<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 7/04/2018
 * Time: 3:53 PM
 */
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

session_start();
include_once 'phpformbuilder/Form.php';
require_once 'phpformbuilder/database/db-connect.php';
require_once 'phpformbuilder/database/Mysql.php';

$spec_id = $_GET["spec_id"];

$names["spec_id"] = "Specimen ID";
$names["family"] = "Family";
$names["genus"] = "Genus";
$names["species"] = "Species";
$names["poll_spore"] = "Pollen/Spore";
$names["grain_arrangement"] = "Grain Arrangement";
$names["grain_morphology"] = "Grain Morphology";
$names["polar_axis_length"] = "Polay Axis Length";
$names["equatorial_axis_length"] = "Equatorial Axis Length";
//$names["***determined***"] = "";
//$names["***determined***"] = "";
$names["equatorial_shape_minor"] = "Equatorial Shape Minor";
$names["polar_shape"] = "Polar Shape";
$names["surface_pattern"] = "Surface Pattern";
$names["wall_thickness"] = "Wall Thickness";
$names["wall_evenness"] = "Wall Evenness";
$names["exine_type"] = "Exine Type";
$names["colporus"] = "Colporus";
$names["L_P"] = "Length parallel to P";
$names["L_E"] = "Length parallel to E";
$names["pore_protrusion"] = "Pore Protrusion";
$names["pore_shape_e"] = "Pore Shape - E View";
$names["pore_shape_size"] = "Pore Shape Size";
$names["pore_shape_p"] = "Pore Shape - P View";
$names["pore_margin"] = "Pore Margin";
$names["colpus_sulcus_length_c"] = "Colpus / Sulcus length (C)";
$names["colpus_sulcus_shape"] = "Colpus / Sulcus shape";
//$names["***determined***"] = "";
$names["colpus_sulcus_margin"] = "Colpus / Sulcus margin";
$names["apocolpium_width_e"] = "Apocolpium Width (e)";
//$names["***determined***"] = "";
$names["trilete_scar_arm_length"] = "Trilete scar arm length";
$names["trilete_scar_shape"] = "Trilete scar shape";
$names["p_sacci_size"] = "P sacci size";
$names["e_sacci_size"] = "E sacci size";
$names["morphology_notes"] = "Morphology Notes";


$db = new Mysql();

$db->selectRows('specimen', array('spec_id' => Mysql::SQLValue($spec_id)), null, null, true, 1);
$specimen = $db->recordsArray()[0];
?>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fontawesome.min.css">
    <link rel="stylesheet" href="css/bsadmin.css">
</head>
<body>
<div class="d-flex">

    <?php
    //require_once "sidebar.php"; // Add Side Nav Bar
    ?>
    <div class="container">
        <div class="row justify-content-center">
            <div style="padding-top: 30px" class="col-md-11 col-lg-10">
<h1>Specimen: <?= $spec_id?></h1>
<table class="table table-bordered">
    <thead><tr><td style="font-weight: bold">Type</td><td style="font-weight: bold">Value</td></tr></thead>
<?php
foreach ($names as $column => $value) {
    echo "<tr><td>".$value."</td><td>".str_replace(",", "<br/>",$specimen[$column])."</td></tr>";
}
?>
</table>
<?php
$dir = new DirectoryIterator($specimen["image_folder"]);
echo "<br /> <p style='font-weight: bold'>Images</p>";
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $image = $fileinfo->getFilename();
        if ($image != "thumbnail") {
            echo '<img style="width: 300px;padding-bottom: 15px;" src="/phpformbuilder/images/uploads/' . $specimen["spec_id"] . '/' . $image . '"><br />';
        }
    }
}
?>
            </div>
        </div>
    </div>
</div>
<?php
require_once "scripts.php"; // Get scripts
?>