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

$db = new Mysql();

$project = $_GET["project"];
$core = $_GET["core"];
$sample = $_GET["sample"];
$date = date("Y-m-d H:i:s");

/* =============================================
    validation if posted
============================================= */

$results = array();

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('search-form-1') === true) {
    // create validator & auto-validate required fields
    $validator = Form::validate('search-form-1');

    if (isset($_POST["add-to-sample"])) {
        $update["spec_id"] = Mysql::SQLValue($_POST['add-to-sample']);
        $update["sample_id"] = Mysql::SQLValue($sample);
        $update["last_update"] = "'" . $date . "'";
        $update["count"] = Mysql::SQLValue(1);
        $db->insertRow('found_specimen', $update);
        if (!empty($db->error())) {
            $msg = '<p class="alert alert-danger">' . $db->error() . '<br>' . $db->getLastSql() . '</p>' . "\n";
        } else {
            $msg = '<p class="alert alert-success">Successfully added to sample !</p>' . " \n";
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
        $i = 0;
        $form->addHtml("<div>");
        foreach ($results as $specimen) {
            $i %= 3;
            if ($i == 0) $form->addHtml('<div class="row">');
            $form->addHtml('<div class="col-md-4">');
            $form->addHtml('<text style="font-weight: bold;padding: 5px">ID: ' . $specimen["spec_id"] . '</text>
                <a href="add_new_specimen.php?project='.$project.'&core='.$core.'&sample='.$sample.'&edit='.$specimen["spec_id"].'" target="_blank"><i class="fa fa-edit" aria-hidden="true"></i></a>
                <a href="specimen_details.php?spec_id='.$specimen["spec_id"].'" target="_blank"><i class="fa fa-info-circle" aria-hidden="true"></i></a>');
            //$form->groupInputs("add-to-sample", "edit");
            $form->addBtn('submit', 'add-to-sample', $specimen["spec_id"], 'Add To Sample <i class="fa fa-plus-circle" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in');
            $image = $specimen["image_folder"].$specimen["primary_image"];
            if (is_file($image)) {
                $form->addHtml('<img style="width: 100%;padding-bottom: 15px;" src="/phpformbuilder/images/uploads/' . $specimen["spec_id"] . '/'.$specimen["primary_image"].'">');
            }
            $form->addHtml('</div>');
            if ($i == 2) $form->addHtml('</div>');
            $i++;
        }
        $form->addHtml('</div><br><br>');
    } else {
        $form->addHtml('<p style="text-align: center; color: red">No samples found</p>');
    }
}

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');
$title = "$project > $core > $sample > Search Sample";
require_once "add_form_html.php";