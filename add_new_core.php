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

/* =============================================
    validation if posted
============================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('add-new-core') === true) {
    $validator = Form::validate('add-new-core');

    if ($validator->hasErrors()) {
        $_SESSION['errors']['user-form'] = $validator->getAllErrors();
    } else {

        $db = new Mysql();
        $update["core_id"] = Mysql::SQLValue($_POST["core_id"]);
        $update["project_name"] = Mysql::SQLValue($project);
        $update["description"] = Mysql::SQLValue($_POST["core_description"]);
        $db->insertRow('cores', $update);
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

$form = new Form('add-new-core', 'horizontal', 'novalidate', 'bs4');

$form->addHelper('Core ID', 'core_id');
$form->addInput('text', 'core_id', '', 'Core ID');

$form->addPlugin('tinymce', '#core_description', 'contact-config');
$form->addTextarea('core_description', '', 'Core Notes');

#######################
# Clear/Save
#######################
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning', 'my-btn-group');
$form->addBtn('submit', 'submit-btn', 1, 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-project', 'bs4');

$title = "$project > Add New Core";
require_once "add_form_html.php";