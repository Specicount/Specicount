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

/* =============================================
    validation if posted
============================================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('add-new-found-sample') === true) {
    if($_POST["submit-btn"] == "export") {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="sample_export_'.date("Ymd").'.csv";');

        $db->selectRows('found_specimen', array('sample_id' => Mysql::SQLValue($sample)));
        $specimen_data = $db->recordsArray();
        // open the "output" stream
        // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
        $f = fopen('php://output', 'w');
        fputcsv($f, array("Specimen ID", "Count"), ",");
        foreach ($specimen_data as $spec) {
            fputcsv($f, array($spec["spec_id"], $spec["count"]), ",");
        }
        header("Refresh:0");
        exit;
    } else {

        $validator = Form::validate('add-new-sample');

        $db->selectRows('samples', array('sample_id' => Mysql::SQLValue($sample)), null, null, true, 1);
        $sample_data = $db->recordsArray()[0];

        if ($validator->hasErrors()) {
            $_SESSION['errors']['user-form'] = $validator->getAllErrors();
        } else if ($sample_data["last_edit"] == $_POST["last_edit"] || empty($sample_data["last_edit"])) { // Was last updated by this device

            $error = false;
            $sub_post = array_slice($_POST, 4);
            foreach ($sub_post as $specimen => $count) {
                if ($specimen != "lycopodium" && $specimen != "charcoal" && $specimen != "last_edit") {
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
$form->addBtn('button', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onClick=window.location.reload()', 'my-btn-group');
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
$form->addHtml("<div>");

if($db->rowCount() > 0) {

    $form->addHtml('<hr>');

    $form->addHtml('<div class="flexbin flexbin-margin">');

    foreach ($specs as $specimen) {
        $image = $specimen["image_folder"].$specimen["primary_image"];
        $form->addHtml('<div id="'.$specimen["spec_id"].'_container" class="flex-container specimen-container">');
        if (is_file($image)) {
            $form->addHtml('<img src="/phpformbuilder/images/uploads/' . $specimen["spec_id"] . '/'.$specimen["primary_image"].'">');
        }
        $form->addHtml('<div class="counter">
                                <p id="'.$specimen["spec_id"].'_counter">' . $specimen["count"] . '</p>
                              </div>');
        $form->addHtml('<div class="overlay">');
        $form->addHtml('<a href="#">
                                <span><i class="fas fa-window-close close-btn"></i></span>
                             </a>');
        $form->addHtml('<text>ID: ' . $specimen["spec_id"] . '</text>
            <a href="add_new_specimen.php?project='.$project.'&core='.$core.'&sample='.$sample.'&edit='.$specimen["spec_id"].'" target="_blank"><i class="fa fa-edit edit-btn"></i></a>
            <a href="specimen_details.php?spec_id='.$specimen["spec_id"].'" target="_blank"><i class="fa fa-info-circle info-btn"></i></a>');
        $form->addBtn('button', $specimen["spec_id"].'_add', 1, '<i class="fa fa-plus"></i>', 'class=btn btn-success, data-style=zoom-in, onclick=add(\''.$specimen["spec_id"].'\');updateCounter(\''.$specimen["spec_id"].'\')');
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

    function updateCounter(spec_id){
        document.getElementById(spec_id+"_counter").innerHTML = document.getElementById(spec_id).value;
    }
    function add(spec_id) {
        document.getElementById(spec_id).value = parseFloat(document.getElementById(spec_id).value) + 1;
    }

    function subtract(spec_id){
        document.getElementById(spec_id).value = parseFloat(document.getElementById(spec_id).value) - 1;
    }
    window.onkeyup = function(e) {
        var key = e.keyCode ? e.keyCode : e.which;

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


    $(document).ready(function() {


        // This uses the hoverIntent jquery plugin to avoid excessive queuing of animations
        // If mouse intends to hover over specimen
        $(".specimen-container").hoverIntent(
        function() {
            let current_overlay = $(".overlay[style*='block']");
            fadeOutOverlay(current_overlay.parent());

            $(this).children(".overlay").fadeIn(200);
            $(this).children(".counter").fadeOut(200);
        },
        function() {
            fadeOutOverlay($(this));
        });

        //Takes
        function fadeOutOverlay(specimen_container) {
            specimen_container.find(".overlay").fadeOut(200);
            let counter = specimen_container.find(".counter");
            counter.fadeIn(200);
        }


        //If close button on overlay clicked
        $(".overlay .close-btn").click(function(){
            fadeOutOverlay($(this).parent().parent().parent().parent());
        })
    });
</script>
