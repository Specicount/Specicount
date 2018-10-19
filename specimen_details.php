<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 7/04/2018
 * Time: 3:53 PM
 *
 * This page is used to describe each specimen extracted from the database.
 * It is in table format and is separate from the rest of the pages.
 * It opens in a new tab and has no sidebar.
 */
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

require_once "classes/Page_Renderer.php";

$specimen_id = $_GET["specimen_id"];

# Specimen details
$names["specimen_id"] = "Specimen ID";
$names["family"] = "Family";
$names["genus"] = "Genus";
$names["species"] = "Species";
$names["poll_spore"] = "Pollen/Spore";
$names["grain_arrangement"] = "Grain Arrangement";
$names["grain_morphology"] = "Grain Morphology";
$names["polar_axis_length_min"] = "Polar Axis Length (Min)";
$names["polar_axis_length_avg"] = "Polar Axis Length (Avg)";
$names["polar_axis_length_max"] = "Polar Axis Length (Max)";
$names["equatorial_axis_length_min"] = "Equatorial Axis Length (Min)";
$names["equatorial_axis_length_avg"] = "Equatorial Axis Length (Avg)";
$names["equatorial_axis_length_max"] = "Equatorial Axis Length (Max)";
$names["size"] = "Size";
//$names["***determined***"] = "";
//$names["***determined***"] = "";
$names["equatorial_shape_major"] = "Equatorial Shape Major";
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
$names["plant_function_type"] = "Plan Function Type";
$names["morphology_notes"] = "Morphology Notes";


$db = new Mysql();

$db->selectRows('specimens', array('specimen_id' => Mysql::SQLValue($_GET['specimen_id']),'project_id' => Mysql::SQLValue($_GET['project_id'])));
$specimen = $db->recordsArray()[0];

$output = "
<h1>Specimen: <?= $specimen_id?></h1>
<table class=\"table table-bordered\">
    <thead><tr><td style=\"font-weight: bold\">Type</td><td style=\"font-weight: bold\">Value</td></tr></thead>";
foreach ($names as $column => $value) {
    $output .= "<tr><td>".$value."</td><td>".str_replace(",", "<br/>",$specimen[$column])."</td></tr>";
}

$output .= "</table>";

if (isset($specimen["image_folder"])) {
    $dir = new DirectoryIterator($specimen["image_folder"]);
    $output .= "<br /> <p style='font-weight: bold'>Images</p>";
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $image = $fileinfo->getFilename();
            if ($image != "thumbnail") {
                $output .= '<img style="width: 300px;padding-bottom: 15px;" src="/phpformbuilder/images/uploads/' . $specimen["project_id"] . '/' . $specimen["specimen_id"] . '/' . $image . '"><br />';
            }
        }
    }
} else {
    storeErrorMsg("Could not find specimen image folder");
}


$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Specimen Details");
$page_render->setPageRestrictions(true,true, false, false, true);
//$page_render->disableNavbar();
$page_render->disableSidebar();
$page_render->setInnerHTML($output);
$page_render->renderPage();