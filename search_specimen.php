<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

require_once $_SERVER["DOCUMENT_ROOT"]."/page-components/functions.php";
use function functions\printDbErrors;

include_once 'phpformbuilder/Form.php';
require_once 'phpformbuilder/database/db-connect.php';
require_once 'phpformbuilder/database/Mysql.php';
require_once "classes/Page_Renderer.php";

$db = new Mysql();

$project_id = $_GET["project_id"];
$core_id = $_GET["core_id"];
$sample_id = $_GET["sample_id"];
$date = date("Y-m-d H:i:s");

/* =============================================
    validation if posted
============================================= */

$results = array();

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('search-form-1') === true) {
    // create validator & auto-validate required fields
    $validator = Form::validate('search-form-1');

    if (isset($_POST["add-to-sample"])) {
        $primary_key_values = explode(",", $_POST["add-to-sample"]);
        $specimen_project_id = trim(base64_decode(str_replace("-", "=", $primary_key_values[0])));
        $specimen_id = trim(base64_decode(str_replace("-", "=", $primary_key_values[1])));
        $update["specimen_id"] = Mysql::SQLValue($specimen_id);
        $update["specimen_project_id"] = Mysql::SQLValue($specimen_project_id);
        $update["sample_id"] = Mysql::SQLValue($sample_id);
        $update["core_id"] = Mysql::SQLValue($core_id);
        $update["project_id"] = Mysql::SQLValue($project_id);
        $update["last_update"] = "'" . $date . "'";
        $update["count"] = Mysql::SQLValue(1);
        $db->insertRow('found_specimens', $update);
        if (!empty($db->error())) {
            if (stripos($db->error(), "Duplicate") !== false) {
                $msg = '<p class="alert alert-danger">Specimen: '.$update["specimen_id"].' has already added to the sample</p>' . "\n";
            } else {
                $msg = '<p class="alert alert-danger">' . $db->error() . '<br>' . $db->getLastSql() . '</p>' . "\n";
            }

        } else {
            # Do concentration curve
            $update_curve["sample_id"] = Mysql::SQLValue($sample_id);
            $update_curve["core_id"] = Mysql::SQLValue($core_id);
            $update_curve["project_id"] = Mysql::SQLValue($project_id);
            $db->query("SELECT SUM(count) as total FROM found_specimens WHERE sample_id = ".$update_curve["sample_id"]." AND core_id = ".$update_curve["core_id"]." AND project_id = ".$update_curve["project_id"]);
            $tally_count = $db->recordsArray()[0]["total"];
            $update_curve["tally_count"] = Mysql::SQLValue($tally_count, "int");
            $db->query("SELECT COUNT(*) as amount FROM found_specimens WHERE sample_id = ".$update_curve["sample_id"]." AND core_id = ".$update_curve["core_id"]." AND project_id = ".$update_curve["project_id"]);
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

    $columns = $db->getColumnNames("specimens");
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
    $project_id_sql = Mysql::SQLValue($project_id);
    $sql = "SELECT * FROM BioBase.specimens WHERE " . implode(" AND ", $col) . " AND project_id=". $project_id_sql;

    //echo $sql;
    $db->query($sql);
    //printDbErrors($db);
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
            $specimen_id_encoded = str_replace("=", "-", trim(base64_encode($specimen["specimen_id"])));
            $specimen_project_id_encoded = str_replace("=", "-", trim(base64_encode($specimen["project_id"])));
            $primary_key_values = $specimen_project_id_encoded . ',' . $specimen_id_encoded;
            $image = $specimen["image_folder"].$specimen["primary_image"];
            $form->addHtml('<div id="'.$specimen_id_encoded.'" class="specimen-container cell"');
            if (is_file($image)) {
                $form->addHtml(' style="background-image:url(\'/phpformbuilder/images/uploads/'.$specimen["specimen_id"].'/'.$specimen["primary_image"].'\');"');
            }
            $form->addHtml('>');
            $form->addHtml('<div id="'.$specimen_id_encoded.'_counter" class="counter"><p id="'.$specimen_id_encoded.'_counter_text">ID: ' . $specimen["specimen_id"] . '</p></div>');
            $form->addHtml('<div id="'.$specimen_id_encoded.'_overlay" class="overlay">');
            $form->addHtml('<text>ID: ' . $specimen["specimen_id"] . '</text>');
            $form->addHtml('<a href="add_new_specimen.php?edit=true&project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-edit edit-btn"></i></a>');
            $form->addHtml('<a href="specimen_details.php?project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-info-circle del-btn"></i></a>');
            $form->addHtml('<a href="#"><span><i id="'.$specimen_id_encoded.'_close" class="fas fa-window-close close-btn"></i></span></a>');
            $form->addBtn('submit', 'add-to-sample', $primary_key_values, 'Add To Sample <i class="fa fa-plus-circle" aria-hidden="true"></i>', 'class=btn btn-success ladda-button add-btn, data-style=zoom-in');
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

$title = "$project_id > $core_id > $sample_id > Search Sample";
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($title);
$page_render->renderPage();
