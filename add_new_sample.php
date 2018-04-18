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

$project = $_GET["project"];
$core = $_GET["core"];

if ($_GET["edit"]) {
    $form_name = 'add-new-sample-edit';
    $name = "Edit Sample ".$_GET["edit"];
} else {
    $form_name = 'add-new-sample';
    $name = "Add New Sample";
}

$db = new Mysql();

/* =============================================
    validation if posted
============================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($form_name) === true) {
    $validator = Form::validate($form_name);

    if ($validator->hasErrors()) {
        $_SESSION['errors'][$form_name] = $validator->getAllErrors();
    } else {

        $update["core_id"] = Mysql::SQLValue($core);
        $update["sample_id"] = Mysql::SQLValue($_POST["sample_id"]);
        $update["analyst_first_name"] = Mysql::SQLValue($_POST["first_name"]);
        $update["analyst_last_name"] = Mysql::SQLValue($_POST["last_name"]);
        $update["start_date"] = Mysql::SQLValue($_POST["start_date"], "date");
        $update["modelled_age"] = Mysql::SQLValue($_POST["modelled_age"]);

        if ($_GET["edit"]) {
            $db->updateRows('samples', $update, array("sample_id" => $update["sample_id"], 'core_id' => Mysql::SQLValue($core)));
        } else {
            $db->insertRow('samples', $update);
        }

        if (!empty($db->error())) {
            $msg = '<p class="alert alert-danger">' . $db->error() . '<br>' . $db->getLastSql() . '</p>' . "\n";
        } else {
            $msg = '<p class="alert alert-success">Database updated successfully !</p>' . " \n";
        }
    }
}

if ($_GET["edit"]) {
    unset($_SESSION['add-new-sample-edit']);
    $sample_id = trim($_GET["edit"]);
    $db->selectRows('samples', array('sample_id' => Mysql::SQLValue($sample_id), 'core_id' => Mysql::SQLValue($core)));
    $sample = $db->recordsArray()[0];
    $_SESSION[$form_name]["sample_id"] = $sample["sample_id"];
    $_SESSION[$form_name]["first_name"] = $sample["analyst_first_name"];
    $_SESSION[$form_name]["last_name"] = $sample["analyst_last_name"];
    $_SESSION[$form_name]["start_date"] = $sample["start_date"];
    $_SESSION[$form_name]["modelled_age"] = $sample["modelled_age"];
}

/* ==================================================
    The Form
================================================== */

$form = new Form($form_name, 'horizontal', 'novalidate', 'bs4');

$form->addHelper('Sample ID', 'sample_id');

if ($_GET["edit"]) {
    $form->addInput('text', 'sample_id', '', 'Sample ID ', 'required, readonly="readonly"');
} else {
    $form->addInput('text', 'sample_id', '', 'Sample ID ', 'required');
}

# Analyst Name
$form->setCols(4, 4);
$form->groupInputs('first_name', 'last_name');
$form->addHelper('First Name', 'first_name');
$form->addInput('text', 'first_name', '', 'Analyst ', 'required');
$form->setCols(0, 4);
$form->addHelper('Last Name', 'last_name');
$form->addInput('text', 'last_name', '', '', '');
$form->setCols(4, 8);

$form->addPlugin('pickadate', '#start_date');
$form->addInput('text', 'start_date', '', 'Start Date ', 'required');

$form->addHelper('Years Old', 'modelled_age');
$form->addInput('number', 'modelled_age', '', 'Modelled Age');

#######################
# Clear/Save
#######################
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning', 'my-btn-group');
$form->addBtn('submit', 'submit-btn', 1, 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');
$title = "$project > $core > $name";
require_once "add_form_html.php";
?>