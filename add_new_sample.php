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

/* =============================================
    validation if posted
============================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('add-new-sample') === true) {
    $validator = Form::validate('add-new-sample');

    if ($validator->hasErrors()) {
        $_SESSION['errors']['user-form'] = $validator->getAllErrors();
    } else {

        $db = new Mysql();

        $update["core_id"] = Mysql::SQLValue($core);
        $update["sample_id"] = Mysql::SQLValue($_POST["sample_id"]);
        $update["analyst_first_name"] = Mysql::SQLValue($_POST["first_name"]);
        $update["analyst_last_name"] = Mysql::SQLValue($_POST["last_name"]);
        $update["start_date"] = Mysql::SQLValue($_POST["start_date"], "date");
        $update["modelled_age"] = Mysql::SQLValue($_POST["modelled_age"]);

        $db->insertRow('samples', $update);
        if (!empty($db->error())) {
            $msg = '<p class="alert alert-danger">' . $db->error() . '<br>' . $db->getLastSql() . '</p>' . "\n";
        } else {
            $msg = '<p class="alert alert-success">Database updated successfully !</p>' . " \n";
        }
    }
}

/* ==================================================
    The Form
================================================== */

$form = new Form('add-new-sample', 'horizontal', 'novalidate', 'bs4');

$form->addHelper('Sample ID', 'sample_id');
$form->addInput('text', 'sample_id', '', 'Sample ID ', 'required');

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
$title = "$project > $core > Add New Sample";
require_once "add_form_html.php";
?>