<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

include_once 'phpformbuilder/Form.php';
require_once 'phpformbuilder/database/db-connect.php';
require_once 'phpformbuilder/database/Mysql.php';
require_once "classes/Page_Renderer.php";

$db = new Mysql();

$project = $_GET["project_id"];
$core = $_GET["core_id"];
$sample = $_GET["sample_id"];
$date = date("Y-m-d H:i:s");

/* =============================================
    validation if posted
============================================= */

$results = array();

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('search-form-1') === true) {
    // create validator & auto-validate required fields
    $validator = Form::validate('search-form-1');

    if (isset($_POST["add-to-sample"])) {
        $specimen = trim(base64_decode(str_replace("-", "=", $_POST["add-to-sample"])));
        $update["specimen_id"] = Mysql::SQLValue($specimen);
        $update["sample_id"] = Mysql::SQLValue($sample);
        $update["core_id"] = Mysql::SQLValue($core);
        $update["project_id"] = Mysql::SQLValue($project);
        $update["last_update"] = "'" . $date . "'";
        $update["count"] = Mysql::SQLValue(1);
        $db->insertRow('found_specimen', $update);
        if (!empty($db->error())) {
            if (stripos($db->error(), "Duplicate") !== false) {
                $msg = '<p class="alert alert-danger">Specimen: '.$update["specimen_id"].' has already added to the sample</p>' . "\n";
            } else {
                $msg = '<p class="alert alert-danger">' . $db->error() . '<br>' . $db->getLastSql() . '</p>' . "\n";
            }

        } else {
            # Do concentration curve
            $update_curve["sample_id"] = Mysql::SQLValue($sample);
            $update_curve["core_id"] = Mysql::SQLValue($core);
            $update_curve["project_id"] = Mysql::SQLValue($project);
            $db->query("SELECT SUM(count) as total FROM found_specimen WHERE sample_id = ".$update_curve["sample_id"]." AND core_id = ".$update_curve["core_id"]." AND project_id = ".$update_curve["project_id"]);
            $tally_count = $db->recordsArray()[0]["total"];
            $update_curve["tally_count"] = Mysql::SQLValue($tally_count, "int");
            $db->query("SELECT COUNT(*) as amount FROM found_specimen WHERE sample_id = ".$update_curve["sample_id"]." AND core_id = ".$update_curve["core_id"]." AND project_id = ".$update_curve["project_id"]);
            $unique_spec = $db->recordsArray()[0]["amount"];
            $update_curve["unique_spec"] = Mysql::SQLValue($unique_spec, "int");
            $db->insertRow('concentration_curve', $update_curve);
            $msg = '<p class="alert alert-success">Successfully added: '.$update["specimen_id"].' to the sample !</p>' . " \n";
        }
    }

    $search = trim($_POST["search-input-1"]);
    $search = explode(" ", $search);
    $search = array_map("trim", $search);
    $search = array_unique($search);

    $columns = $db->getColumnNames("specimen");
    $col = array();
    foreach ($search as $s) {
        if (!empty($s)) {
            $s = Mysql::SQLValue($s);
            $query = array();
            if (strpos($s, "*") !== false) {
                $s = str_replace("*", "%", $s);
                $q = "LIKE $s";
            } else {
                $q = "= $s";
            }
            foreach ($columns as $name) {
                $query[] = "$name " . $q;
            }
            $col[] = "(" . implode(" OR ", $query) . ")";
        }
    }
    $sql = "SELECT * FROM BioBase.specimen WHERE " . implode(" AND ", $col);
    //echo $sql;
    $db->query($sql);
    if ($db->rowCount() > 0) {
        $results = $db->recordsArray();
    }
}

/* ==================================================
    The Form
================================================== */

$form = new Form('search-form-1', 'vertical', 'class=mb-5, novalidate', 'bs4');
$options = array(
    'elementsWrapper' => '<div class="input-group"></div>'
);
$form->setOptions($options);
$form->groupInputs('search-input-1', 'search-btn-1');
$form->addInputWrapper('<span class="input-group-btn"></span>', 'search-btn-1');
$form->addInput('text', 'search-input-1', '', '', 'placeholder=Search Attributes Here ...');
$form->addBtn('submit', 'search-btn-1', 1, '<i class="fa fa-search" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in');

$form->addHtml('<br><br>');

# Add in results
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($results)) {
        $form->addHtml('<div class="square-grid">');
        foreach ($results as $specimen) {
            $specimen_name = $specimen["specimen_id"];
            $specimen["specimen_id"] = str_replace("=", "-", trim(base64_encode($specimen["specimen_id"])));
            $image = $specimen["image_folder"].$specimen["primary_image"];
            $form->addHtml('<div id="'.$specimen["specimen_id"].'" class="specimen-container cell"');
            if (is_file($image)) {
                $form->addHtml(' style="background-image:url(\'/phpformbuilder/images/uploads/'.$specimen_name.'/'.$specimen["primary_image"].'\');"');
            }
            $form->addHtml('>');
            $form->addHtml('<div id="'.$specimen["specimen_id"].'_counter" class="counter"><p id="'.$specimen["specimen_id"].'_counter_text">ID: ' . $specimen_name . '</p></div>');
            $form->addHtml('<div id="'.$specimen["specimen_id"].'_overlay" class="overlay">');
            $form->addHtml('<text>ID: ' . $specimen_name . '</text>');
            $form->addHtml('<a href="add_new_specimen.php?edit=true&project='.$project.'&core='.$core.'&sample='.$sample.'&specimen_id='.$specimen_name.'" target="_blank"><i class="fa fa-edit edit-btn"></i></a>');
            $form->addHtml('<a href="specimen_details.php?specimen_id='.$specimen_name.'" target="_blank"><i class="fa fa-info-circle del-btn"></i></a>');
            $form->addHtml('<a href="#"><span><i id="'.$specimen["specimen_id"].'_close" class="fas fa-window-close close-btn"></i></span></a>');
            $form->addBtn('submit', 'add-to-sample', $specimen["specimen_id"], 'Add To Sample <i class="fa fa-plus-circle" aria-hidden="true"></i>', 'class=btn btn-success ladda-button add-btn, data-style=zoom-in');
            $form->addHtml('</div>');
            $form->addHtml('</div>');
        }
        $form->addHtml('</div><br><br>');
    } else {
        $form->addHtml('<p style="text-align: center; color: red">No samples found</p>');
    }
}

// jQuery validation

$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');

$title = "$project > $core > $sample > Search Sample";
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($title);
$page_render->renderPage();
