<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

require_once "classes/Page_Renderer.php";

$date = date("Y-m-d H:i:s");

$db = new Mysql();

$project = $_GET["project_id"];
$core = $_GET["core_id"];
$sample = $_GET["sample_id"];

/* =============================================
    validation if posted
============================================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('add-new-found-sample') === true) {

    $db->selectRows('samples', array('sample_id' => Mysql::SQLValue($sample), 'core_id' => Mysql::SQLValue($core), 'project_id' => Mysql::SQLValue($project)), null, null, true, 1);
    $sample_data = $db->recordsArray()[0];

    if($_POST["submit-btn"] == "export") {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="sample_export_'.date("Ymd").'.csv";');

        $db->query("SELECT * FROM found_specimens JOIN specimens USING(specimen_id) WHERE sample_id = ".Mysql::SQLValue($sample)." ORDER BY `count` DESC");
        $specimen_data = $db->recordsArray();

        // Create a PHP output stream for the user to download
        $f = fopen('php://output', 'w');
        fputcsv($f, array("Specimen", "Family", "Genus", "Species", "Plan Function Type", "Count"), ",");
        fputcsv($f, array("Lycopodium", "", "", "", "", $sample_data["lycopodium"]), ",");
        fputcsv($f, array("Charcoal", "", "", "", "", $sample_data["charcoal"]), ",");

        foreach ($specimen_data as $spec) {
            fputcsv($f, array($spec["specimen_id"], $spec["family"], $spec["genus"], $spec["species"], $spec["plant_function_type"], $spec["count"]), ",");
        }

        // Refresh page back to what it was before
        header("Refresh:0");
        exit;
    } else if ($_POST['delete-from-sample']) {
        $primary_key_values = explode(",", $_POST["delete-from-sample"]);
        $specimen_project_id = trim(base64_decode(str_replace("-", "=", $primary_key_values[0])));
        $specimen_id = trim(base64_decode(str_replace("-", "=", $primary_key_values[1])));

        $filter = array();
        $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
        $filter["core_id"] = Mysql::SQLValue($_GET["core_id"]);
        $filter["sample_id"] = Mysql::SQLValue($_GET["sample_id"]);
        $filter["specimen_project_id"] = Mysql::SQLValue($specimen_project_id);
        $filter["specimen_id"] = Mysql::SQLValue($specimen_id);


        $db->deleteRows('found_specimens', $filter);
        if($db->error()) {
            $msg = '<p class="alert alert-danger">'.$specimen.' could not be deleted !</p>' . " \n";
        } else {
            $msg = '<p class="alert alert-success">'.$specimen.' deleted from sample successfully !</p>' . " \n";
        }
    } else {
        if ($sample_data["last_edit"] == $_POST["last_edit"] || empty($sample_data["last_edit"])) { // Was last updated by this device
            $error = false;
            $sub_post = array_slice($_POST, 4);
            foreach ($sub_post as $specimen => $count) {
                if ($specimen != "lycopodium" && $specimen != "charcoal" && $specimen != "last_edit") {
                    $specimen = trim(base64_decode(str_replace("-", "=", $specimen)));
                    $update = array();
                    $update["specimen_id"] = Mysql::SQLValue($specimen);
                    $update["sample_id"] = Mysql::SQLValue($sample);
                    $update["last_update"] = "'" . $date . "'";
                    $update["count"] = Mysql::SQLValue($count);

                    $db->updateRows('found_specimens', $update, array('sample_id' => Mysql::SQLValue($sample), 'core_id' => Mysql::SQLValue($core),
                        'project_id' => Mysql::SQLValue($project), "specimen_id" => Mysql::SQLValue($specimen)));
                    if (!empty($db->error())) $error = true;
                } else {
                    if ($specimen == "last_edit") {
                        $db->updateRows('samples',
                            array("last_edit" => "'" . $date . "'"),
                            array("sample_id" => Mysql::SQLValue($sample)));
                    } else {
                        $db->updateRows('samples',
                            array($specimen => Mysql::SQLValue($count)),
                            array("sample_id" => Mysql::SQLValue($sample)));
                    }
                    if (!empty($db->error())) $error = true;
                }
            }

            if ($_POST["submit-btn"] == "reorder") {
                $sql = "UPDATE BioBase.found_specimens SET `order` = `count` WHERE sample_id = " . Mysql::SQLValue($sample);
                $db->query($sql);
                if (!empty($db->error())) $error = true;
            }

            if ($error) {
                $msg = '<p class="alert alert-danger">Error updating samples, please try re-saving</p>' . "\n";
            } else {
                $msg = '<p class="alert alert-success">Database updated successfully !</p>' . " \n";
            }
        } else { // Was updated somewhere else
            $msg = '<p class="alert alert-danger">Updated by another device, please try again</p>' . "\n";
        }
    }
}

# Get sample data
$db->selectRows('samples', array('sample_id' => Mysql::SQLValue($sample), 'core_id' => Mysql::SQLValue($core), 'project_id' => Mysql::SQLValue($project)), null, null, true, 1);
$sample_data = $db->recordsArray()[0];
if ($db->error()){
    $msg = '<p class="alert alert-danger">Sample data not found</p>' . "\n";
}

/* ==================================================
    The Form
================================================== */

$form = new Form('add-new-found-sample', 'vertical', 'class=mb-5, novalidate', 'bs4');


#######################
# Sample grid
#######################
$form->addHtml("<input type=\"hidden\" name=\"last_edit\" value=\"".$sample_data["last_edit"]."\">");
$form->addHtml("<div class='row'>");
$form->addHtml("<div class='col-sm'>");
#######################
# Clear/Save
#######################
$form->addBtn('submit', 'submit-btn', "save", 'Save <i class="fa fa-save append" aria-hidden="true"></i>', 'class=btn btn-success, data-style=zoom-in', 'my-btn-group');
$form->addBtn('button', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onClick=reload()', 'my-btn-group');
$form->addBtn('submit', 'submit-btn', "export", 'Export <i class="fa fa-download append" aria-hidden="true"></i>', 'class=btn btn-info', 'my-btn-group');
$form->addBtn('submit', 'submit-btn', "reorder", 'Reorder and Save <i class="fa fa-sync append" aria-hidden="true"></i>', 'class=btn btn-success', 'my-btn-group');
$form->printBtnGroup('my-btn-group');

# Lycopodium

$form->addHtml('<div style="display:inline-block; max-width:200px; padding-right:10px;">');
$form->addHtml('<text style="font-weight: bold;padding: 5px">Lycopodium</text>');
$form->addHtml("<table style='width: 100%'>");
$form->addHtml("<tr style=\"vertical-align:top\"><td style='text-align: left'>");
$form->addBtn('button', 'lycopodium_subtract', 1, '<i class="fa fa-minus" aria-hidden="true"></i>', 'class=btn btn-success sp_count, data-style=zoom-in, onclick=subtract(\'lycopodium\')');
$form->addHtml("</td><td>");
$form->addInput('number', 'lycopodium', $sample_data["lycopodium"], '', 'required');
$form->addHtml("</td><td style='text-align: right'>");
$form->addBtn('button', 'lycopodium_add', 1, '<i class="fa fa-plus" aria-hidden="true"></i>', 'class=btn btn-success, data-style=zoom-in, onclick=add(\'lycopodium\')');
$form->addHtml("</td></tr></table>");
$form->addHtml('</div>');

# Charcoal
$form->addHtml('<div style="display:inline-block; max-width:200px; padding-right:10px;">');
$form->addHtml('<text style="font-weight: bold;padding: 5px">Charcoal</text>');
$form->addHtml("<table style='width: 100%'>");
$form->addHtml("<tr style=\"vertical-align:top\"><td style='text-align: left'>");
$form->addBtn('button', 'charcoal_subtract', 1, '<i class="fa fa-minus" aria-hidden="true"></i>', 'class=btn btn-success sp_count, data-style=zoom-in, onclick=subtract(\'charcoal\')');
$form->addHtml("</td><td>");
$form->addInput('number', 'charcoal', $sample_data["charcoal"], '', 'required');
$form->addHtml("</td><td style='text-align: right'>");
$form->addBtn('button', 'charcoal_add', 1, '<i class="fa fa-plus" aria-hidden="true"></i>', 'class=btn btn-success, data-style=zoom-in, onclick=add(\'charcoal\')');
$form->addHtml("</td></tr></table>");
$form->addHtml('</div>');

$form->addHtml("</div><div class='col-sm'>");

// Concentration curve div
$form->addHtml("<div style='height: 350px' id=\"chart_div\"></div><br/>");

$form->addHtml("</div></div>");


$db->query("SELECT * FROM found_specimens JOIN specimens USING(specimen_id) WHERE sample_id=".Mysql::SQLValue($sample)." ORDER BY `order` DESC");
$specs = array();
$specs = $db->recordsArray();

if($db->rowCount() > 0) {

    //$form->addHtml('<hr>');

    $form->addHtml('<div class="square-grid">');

    foreach ($specs as $specimen) {
        $specimen_id_encoded = str_replace("=", "-", trim(base64_encode($specimen["specimen_id"])));
        $specimen_project_id_encoded = str_replace("=", "-", trim(base64_encode($specimen["project_id"])));
        $primary_key_values = $specimen_project_id_encoded . ',' . $specimen_id_encoded;
        $image = $specimen["image_folder"].$specimen["primary_image"];
        $form->addHtml('<div id="'.$specimen_id_encoded.'" class="specimen-container cell"');
        if (is_file($image)) {
            $form->addHtml(' style="background-image:url(\'/phpformbuilder/images/uploads/'.$specimen["specimen_id"].'/'.$specimen["primary_image"].'\');"');
        }
        $form->addHtml('>');
        //$form->addHtml('<div id="'.$specimen["specimen_id"].'_imageblur" class="imageblur" style="background-image:url(\'/phpformbuilder/images/uploads/'.$specimen_name.'/'.$specimen["primary_image"].'\');"></div>');
        $form->addHtml('<div id="'.$specimen_id_encoded.'_counter" class="counter"><p id="'.$specimen_id_encoded.'_counter_text">' . $specimen["count"] . '</p></div>');
        $form->addHtml('<div id="'.$specimen_id_encoded.'_overlay" class="overlay">');
        $form->addHtml('<text>ID: ' . $specimen["specimen_id"] . '</text>');
        $form->addHtml('<a href="add_new_specimen.php?edit=true&project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-edit edit-btn"></i></a>');
        $form->addHtml('<a href="specimen_details.php?project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-info-circle del-btn"></i></a>');
        $form->addHtml('<a href="#"><span><i id="'.$specimen_id_encoded.'_close" class="fas fa-window-close close-btn"></i></span></a>');
        $form->addBtn('button', 'add-to-count', 1, '<i class="fa fa-plus"></i>', 'class=btn btn-success add-btn, data-style=zoom-in, onclick=add(\''.$specimen_id_encoded.'\');updateCounter(\''.$specimen_id_encoded.'\')');
        $form->addBtn('submit', 'delete-from-sample', $primary_key_values, ' <i class="fa fa-trash"></i>', 'class=btn btn-danger del-btn, data-style=zoom-in, onclick=return confirm(\'Are you sure you want to delete this specimen from the sample?\')');
        $form->addInput('number', $specimen_id_encoded, $specimen["count"], '', 'required onchange=updateCounter(\''.$specimen_id_encoded.'\')');
        $form->addHtml('</div>');
        $form->addHtml('</div>');
    }
    $form->addHtml('</div><br><br>');

} else {
    $form->addHtml('<p style="font-style: italic; margin-top:-200px;">No specimens added to this sample</p>');
}

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle("$project > $core > $sample > Sample Count");
$page_render->renderPage();

// Add concentration curve scripts
require_once "concentration.php";
?>
<script>

    // Undo changes for the sample (only if not previously saved)
    function reload(){
        if (confirm('Are you sure you want to reset the sample?')) {
            window.location.reload();
        }
    }

    // Update counter text on hover
    function updateCounter(specimen_id){

        document.getElementById(specimen_id+"_counter_text").innerHTML = document.getElementById(specimen_id).value;
    }

    // Add to counter
    function add(specimen_id) {
        document.getElementById(specimen_id).value = parseFloat(document.getElementById(specimen_id).value) + 1;
    }

    // Subtract from counter
    function subtract(specimen_id){
        document.getElementById(specimen_id).value = parseFloat(document.getElementById(specimen_id).value) - 1;
    }

    // Enable key presses for counter
    window.onkeyup = function(e) {
        // noinspection JSAnnotator
        let key = e.keyCode ? e.keyCode : e.which;
        <?php
        $keys = str_split("qwertyuiopasdfghjklzxcvbnm"); // hotkeys
        $i = 0;
        foreach ($keys as $k) {
            $specimen_id = str_replace("=", "-", trim(base64_encode($specs[$i]["specimen_id"])));
            echo "if (key == ".(ord($k) - 32).") {
                    add('".$specimen_id."');
                    updateCounter('".$specimen_id."');
                  }";
            $i++;
            if ($i >= count($specs)) break;
        }
        ?>
    };
</script>
