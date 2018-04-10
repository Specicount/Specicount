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

/* =============================================
    validation if posted
============================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('add-new-project') === true) {
    // create validator & auto-validate required fields
    $validator = Form::validate('add-new-project');

    if ($validator->hasErrors()) {
        $_SESSION['errors']['user-form'] = $validator->getAllErrors();
    } else {
        $db = new Mysql();

        $update["project_name"] = Mysql::SQLValue($_POST["project_name"]);
        $update["biorealm"] = Mysql::SQLValue($_POST["biorealm"]);
        $update["country"] = Mysql::SQLValue($_POST["country"]);
        $update["region"] = Mysql::SQLValue($_POST["region"]);

        $db->insertRow('projects', $update);
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

$form = new Form('add-new-project', 'horizontal', 'novalidate', 'bs4');


$form->startFieldset('Project Data');
$form->setCols(6, 6);
$form->groupInputs('project_name', 'biorealm');
$form->addHelper('Project Name', 'project_name');
$form->addInput('text', 'project_name', '', '', 'required'); // Need to have warning for code
$form->setCols(0, 6);
$form->addHelper('Biorealm', 'biorealm');
$form->addInput('text', 'biorealm', '', '', '');
$form->setCols(6, 6);

$form->setCols(6, 6);
$form->groupInputs('country', 'region');
$form->addHelper('Country', 'country');
$form->addCountrySelect('country', '', '', array('flag_size' => 16, 'return_value' => 'code', 'placeholder' => 'Select your country'));
$form->setCols(0, 6);
$form->addHelper('Region', 'region');
$form->addInput('text', 'region', '', '', '');
$form->setCols(6, 6);

$form->endFieldset();

#######################
# Clear/Save
#######################
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning', 'my-btn-group');
$form->addBtn('submit', 'submit-btn', 1, 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-project', 'bs4');
$title = "Add New Project";
require_once "add_form_html.php";
?>