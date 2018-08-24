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

if ($_GET["edit"]) {
    $form_name = 'add-new-project-edit';
    $name = "Edit Project ".$_GET["edit"];
} else {
    $form_name = 'add-new-project';
    $name = "Add New Project";
}

$db = new Mysql();

/* =============================================
    validation if posted
============================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($form_name) === true) {
    if ($_POST["submit-btn"] == "delete") {
        # Delete from both cores table
        $db->deleteRows('projects', array("project_name" => Mysql::SQLValue($_POST["project_name"], "text")));
        if ($db->error()) {
            $msg = '<p class="alert alert-danger">Could not delete project, please make sure all cores are deleted inside</p>' . '\n';
        } else {
            $msg = '<p class="alert alert-success">Project deleted successfully !</p>' . ' \n';
            header("Location: index.php");
        }
    } else {
        $validator = Form::validate($form_name);

        if ($validator->hasErrors()) {
            $_SESSION['errors'][$form_name] = $validator->getAllErrors();
        } else {

            $update["project_name"] = Mysql::SQLValue($_POST["project_name"]);
            $update["biorealm"] = Mysql::SQLValue($_POST["biorealm"]);
            $update["country"] = Mysql::SQLValue($_POST["country"]);
            $update["region"] = Mysql::SQLValue($_POST["region"]);

            if ($_GET["edit"]) {
                $db->updateRows('projects', $update, array("project_name" => $update["project_name"]));
            } else {
                $db->insertRow('projects', $update);
            }

            if (!empty($db->error())) {
                $msg = '<p class="alert alert-danger">' . $db->error() . '<br>' . $db->getLastSql() . '</p>' . "\n";
            } else {
                $msg = '<p class="alert alert-success">Database updated successfully !</p>' . " \n";
            }
        }
    }
}

if ($_GET["edit"]) {
    unset($_SESSION['add-new-project-edit']);
    $project_name = trim($_GET["edit"]);
    $db->selectRows('projects', array('project_name' => Mysql::SQLValue($project_name)));
    $project = $db->recordsArray()[0];
    $_SESSION[$form_name]["project_name"] = $project["project_name"];
    $_SESSION[$form_name]["biorealm"] = $project["biorealm"];
    $_SESSION[$form_name]["country"] = $project["country"];
    $_SESSION[$form_name]["region"] = $project["region"];
}

/* ==================================================
    The Form
================================================== */

$form = new Form($form_name, 'horizontal', 'novalidate', 'bs4');


$form->startFieldset('Project Data');
$form->setCols(6, 6);
$form->groupInputs('project_name', 'biorealm');

$form->addHelper('Project Name', 'project_name');
if ($_GET["edit"]) {
    $form->addInput('text', 'project_name', '', '', 'required, readonly="readonly"');
} else {
    $form->addInput('text', 'project_name', '', '', 'required'); // Need to have warning for code
}
$form->setCols(0, 6);

$form->addHelper('Biorealm', 'biorealm');
$form->addOption('biorealm', '',  'Choose one ...', '', 'disabled, selected');
$form->addOption('biorealm', 'Nearctic',  'Nearctic', '', '');
$form->addOption('biorealm', 'Palearctic',  'Palearctic', '', '');
$form->addOption('biorealm', 'Afrotropic',  'Afrotropic', '', '');
$form->addOption('biorealm', 'Indomalaya',  'Indomalaya', '', '');
$form->addOption('biorealm', 'Australasia',  'Australasia', '', '');
$form->addOption('biorealm', 'Neotropic',  'Neotropic', '', '');
$form->addOption('biorealm', 'Oceania',  'Oceania', '', '');
$form->addOption('biorealm', 'Antarctic',  'Antarctic', '', '');
$form->addSelect('biorealm', '', 'class=select2, data-width=100%');

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
$form->addBtn('submit', 'submit-btn', 1, 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'submit-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this project?\')', 'my-btn-group');
}
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-project', 'bs4');
$title = $name;
require_once "add_form_html.php";
?>