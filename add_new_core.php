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

$db = new Mysql();

if ($_GET["edit"]) {
    $form_name = 'add-new-core-edit';
    $name = "Edit Core ".$_GET["edit"];
} else {
    $form_name = 'add-new-core';
    $name = "Add New Core";
}

/* =============================================
    validation if posted
============================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($form_name) === true) {
    if ($_POST["submit-btn"] == "delete") {
        # Delete from both cores table
        $db->deleteRows('cores', array("core_id" => Mysql::SQLValue($_POST["core_id"], "text")));
        if ($db->error()) {
            $msg = '<p class="alert alert-danger">Could not delete core, please make sure all samples are deleted inside</p>' . "\n";
        } else {
            $msg = '<p class="alert alert-success">Core deleted successfully !</p>' . " \n";
            header("Location: index.php");
        }
    } else {
        $validator = Form::validate($form_name);

        if ($validator->hasErrors()) {
            $_SESSION['errors'][$form_name] = $validator->getAllErrors();
        } else {
            $update["core_id"] = Mysql::SQLValue($_POST["core_id"]);
            $update["project_name"] = Mysql::SQLValue($project);
            $update["description"] = Mysql::SQLValue($_POST["core_description"]);

            if ($_GET["edit"]) {
                $db->updateRows('cores', $update, array("core_id" => $update["core_id"], 'project_name' => $update["project_name"]));
            } else {
                $db->insertRow('cores', $update);
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
    unset($_SESSION['add-new-core-edit']);
    $core_id = trim($_GET["edit"]);
    $db->selectRows('cores', array("core_id" => Mysql::SQLValue($core_id), 'project_name' => Mysql::SQLValue($project)));
    $core = $db->recordsArray()[0];
    $_SESSION[$form_name]["core_id"] = $core["core_id"];
    $_SESSION[$form_name]["core_description"] = $core["description"];
}

/* ==================================================
    The Form
================================================== */

$form = new Form($form_name, 'horizontal', 'novalidate', 'bs4');

$form->addHelper('Core ID', 'core_id');

if ($_GET["edit"]) {
    $form->addInput('text', 'core_id', '', 'Core ID ', 'required, readonly="readonly"');
} else {
    $form->addInput('text', 'core_id', '', 'Core ID');
}


$form->addPlugin('tinymce', '#core_description', 'contact-config');
$form->addTextarea('core_description', '', 'Core Notes');

#######################
# Clear/Save
#######################
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'submit-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this core?\')', 'my-btn-group');
}
$form->addBtn('submit', 'submit-btn', 1, 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-project', 'bs4');

$title = "$project > $name";
require_once "add_form_html.php";