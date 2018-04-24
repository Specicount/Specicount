<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

session_start();
include_once 'phpformbuilder/Form.php';
require_once 'phpformbuilder/database/db-connect.php';
require_once 'phpformbuilder/database/Mysql.php';

$date = date("Y-m-d H:i:s");

$db = new Mysql();

$project = $_GET["project"];
$core = $_GET["core"];
$sample = $_GET["sample"];

// TODO plant_function_type

/* =============================================
    validation if posted
============================================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('add-new-found-sample') === true) {
    if($_POST["submit-btn"] == "export") {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="sample_export_'.date("Ymd").'.csv";');

        $db->query("SELECT * FROM found_specimen JOIN specimen USING(spec_id) WHERE sample_id = ".Mysql::SQLValue($sample));
        $specimen_data = $db->recordsArray();
        // open the "output" stream
        // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
        $f = fopen('php://output', 'w');
        fputcsv($f, array("Specimen", "Family", "Genus", "Species", "Plan Function Type", "Count"), ",");
        foreach ($specimen_data as $spec) {
            fputcsv($f, array($spec["spec_id"], $spec["family"], $spec["genus"], $spec["species"], $spec["plant_function_type"], $spec["count"]), ",");
        }
        header("Refresh:0");
        exit;
    } else if ($_POST['delete-from-sample']) {
        $db->deleteRows('found_specimen', array('spec_id' => Mysql::SQLValue($_POST['delete-from-sample'])));
        if($db->error()) {
            $msg = '<p class="alert alert-danger">'.$_POST['delete-from-sample'].' could not be deleted !</p>' . " \n";
        } else {
            $msg = '<p class="alert alert-success">'.$_POST['delete-from-sample'].' deleted successfully !</p>' . " \n";
        }
    } else {

        $db->selectRows('samples', array('sample_id' => Mysql::SQLValue($sample)), null, null, true, 1);
        $sample_data = $db->recordsArray()[0];

        if ($sample_data["last_edit"] == $_POST["last_edit"] || empty($sample_data["last_edit"])) { // Was last updated by this device
            $error = false;
            $sub_post = array_slice($_POST, 4);
            foreach ($sub_post as $specimen => $count) {
                if ($specimen != "lycopodium" && $specimen != "charcoal" && $specimen != "last_edit") {
                    $specimen = trim(base64_decode(str_replace("-", "=", $specimen)));
                    $update = array();
                    $update["spec_id"] = Mysql::SQLValue($specimen);
                    $update["sample_id"] = Mysql::SQLValue($sample);
                    $update["last_update"] = "'" . $date . "'";
                    $update["count"] = Mysql::SQLValue($count);

                    $db->updateRows('found_specimen', $update, array("sample_id" => Mysql::SQLValue($sample), "spec_id" => Mysql::SQLValue($specimen)));
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
                $sql = "UPDATE BioBase.found_specimen SET `order` = `count` WHERE sample_id = " . Mysql::SQLValue($sample);
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
$db->selectRows('samples', array('sample_id' => Mysql::SQLValue($sample)), null, null, true, 1);
$sample_data = $db->recordsArray()[0];
if ($db->error()){
    $msg = '<p class="alert alert-danger">Sample data not found</p>' . "\n";
}

/* ==================================================
    The Form
================================================== */

$form = new Form('add-new-found-sample', 'vertical', 'class=mb-5, novalidate', 'bs4');




//$spec = $specimen["sample_id"];
/*$form->setCols(0, 4);

$form->groupInputs('test1', 'test2', 'test3');
//$form->addHtml("<p style='text-align: center'>Spec ID: SPI1</p>");
//$form->addHtml("<p style='text-align: center'>Spec ID: SPI2</p>");
$form->addInput('number', 'test2', '', '', 'required');
//$form->addHtml("<p style='text-align: center'>Spec ID: SPI3</p>");
$form->addInput('number', 'test3', '', '', 'required');
$form->setCols(4, 8);*/

//$qry = "SELECT * FROM BioBase.found_specimen WHERE sample_id = '$sample'";
//$db->selectRows('found_specimen', array('sample_id' => Mysql::SQLValue($sample)), null, "order", false);



#######################
# Sample grid
#######################


$form->addHtml("<input type=\"hidden\" name=\"last_edit\" value=\"".$sample_data["last_edit"]."\">");

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

$db->query("SELECT * FROM found_specimen JOIN specimen USING(spec_id) WHERE sample_id=".Mysql::SQLValue($sample)." ORDER BY `order` DESC");
$specs = array();
$specs = $db->recordsArray();

if($db->rowCount() > 0) {

    $form->addHtml('<hr>');

    $form->addHtml('<div class="square-grid">');

    foreach ($specs as $specimen) {
        $specimen_name = $specimen["spec_id"];
        $specimen["spec_id"] = str_replace("=", "-", trim(base64_encode($specimen["spec_id"])));
        $image = $specimen["image_folder"].$specimen["primary_image"];
        $form->addHtml('<div id="'.$specimen["spec_id"].'" class="specimen-container cell"');
        if (is_file($image)) {
            $form->addHtml(' style="background-image:url(\'/phpformbuilder/images/uploads/'.$specimen_name.'/'.$specimen["primary_image"].'\');"');
        }
        $form->addHtml('>');
        $form->addHtml('<div id="'.$specimen["spec_id"].'_counter" class="counter"><p id="'.$specimen["spec_id"].'_counter_text">' . $specimen["count"] . '</p></div>');
        $form->addHtml('<div id="'.$specimen["spec_id"].'_overlay" class="overlay">');
        $form->addHtml('<text>ID: ' . $specimen_name . '</text>');
        $form->addHtml('<a href="add_new_specimen.php?project='.$project.'&core='.$core.'&sample='.$sample.'&edit='.$specimen["spec_id"].'" target="_blank"><i class="fa fa-edit edit-btn"></i></a>');
        $form->addHtml('<a href="specimen_details.php?spec_id=\''.$specimen["spec_id"].'\'" target="_blank"><i class="fa fa-info-circle info-btn"></i></a>');
        $form->addHtml('<a href="#"><span><i id="'.$specimen["spec_id"].'_close" class="fas fa-window-close close-btn"></i></span></a>');
        $form->addBtn('button', 'add-to-count', 1, '<i class="fa fa-plus"></i>', 'class=btn btn-success add-btn, data-style=zoom-in, onclick=add(\''.$specimen["spec_id"].'\');updateCounter(\''.$specimen["spec_id"].'\')');
        $form->addBtn('submit', 'delete-from-sample', $specimen["spec_id"], ' <i class="fa fa-trash"></i>', 'class=btn btn-danger ladda-button del-btn, data-style=zoom-in, onclick=confirm(\'Are you sure you want to delete this specimen from the sample?\')');
        $form->addInput('number', $specimen["spec_id"], $specimen["count"], '', 'required onchange=updateCounter(\''.$specimen["spec_id"].'\')');
        $form->addHtml('</div>');
        $form->addHtml('</div>');

    }
    $form->addHtml('</div><br><br>');

} else {
    $form->addHtml('<p style="font-style: italic">No specimens added to this sample</p>');
}

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');

$title = "$project > $core > $sample > Sample Count";
require_once "add_form_html.php";
?>
<script>

    function reload(){
        if (confirm('Are you sure you want to reset the sample?')) {
            window.location.reload();
        }
    }

    function updateCounter(spec_id){
        document.getElementById(spec_id+"_counter_text").innerHTML = document.getElementsByName(spec_id)[0].value;
    }

    function add(spec_id) {
        document.getElementsByName(spec_id)[0].value = parseFloat(document.getElementsByName(spec_id)[0].value) + 1;
    }

    function subtract(spec_id){
        document.getElementsByName(spec_id)[0].value = parseFloat(document.getElementsByName(spec_id)[0].value) - 1;
    }
    window.onkeyup = function(e) {
        let key = e.keyCode ? e.keyCode : e.which;

        <?php
        $keys = str_split("qwertyuiopasdfghjklzxcvbnm"); // hotkeys
        $i = 0;
        foreach ($keys as $k) {
            echo "if (key == ".(ord($k) - 32).") {
                   document.getElementById('".$specs[$i]["spec_id"]."').value = parseFloat(document.getElementById('".$specs[$i]["spec_id"]."').value) + 1;
                  }";
            $i++;
            if ($i >= count($specs)) break;
        }
        ?>
    };
</script>
