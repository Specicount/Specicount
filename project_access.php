<?php
/**
 * Created by IntelliJ IDEA.
 * User: Elliott
 * Date: 12/09/2018
 * Time: 11:28 PM
 */

use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

use function functions\printDbErrors;
require_once "classes/Page_Renderer.php";

$db = new Mysql();
$form_name = "project-access";
$table_name = "user_project_access";
unset($_SESSION[$form_name]); // To ensure that other users aren't accidentally added as admin

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($form_name) === true) {


     if ($_POST['submit-btn'] == "add-user") {
        // If posted form has been filled out correctly
        $validator = Form::validate($form_name);
        if ($validator->hasErrors()) {
            //print_r($_SESSION[$form_name]);
            $_SESSION['errors'][$form_name] = $validator->getAllErrors();
            print_r($validator->getAllErrors());
        } else {
            $update["project_id"] = Mysql::SQLValue($_GET["project_id"]);
            $update["username"] = Mysql::SQLValue($_POST["add_username"]);
            $update["access_level"] = Mysql::SQLValue($_POST["add_access_level"]);
            $db->insertRow($table_name, $update);
            printDbErrors($db, "Successfully added " . $update["username"] . " to the project!");
        }
    } else if ($_POST['submit-btn'] == "save") {
        foreach (array_keys($_POST) as $key) {
            if (strpos($key, "access_level") !== false) {
                $username = explode(',',$key)[1];
                $access_level = $_POST[$key];

                $update["project_id"] = Mysql::SQLValue($_GET["project_id"]);
                $update["username"] = Mysql::SQLValue($username);
                $update["access_level"] = Mysql::SQLValue($access_level);

                $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
                $filter["username"] = Mysql::SQLValue($username);

                $db->updateRows($table_name, $update, $filter);
            }


        }
        printDbErrors($db, "Changes have been saved!");
    }
     // If the delete button was pressed
    else {
        $username = $_POST["submit-btn"];
        $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
        $filter["username"] = Mysql::SQLValue($username);
        $db->deleteRows($table_name, $filter);
        printDbErrors($db, "Successfully deleted " . $username . " from access list!", "Failed to delete " . $username . " from access list");
    }
}




/* ==================================================
    The Form
================================================== */

$form = new Form($form_name, 'horizontal', 'novalidate', 'bs4');
$form->setOptions(array('buttonWrapper'=>'')); // So that the button can be printed on the same row as the other inputs

$form->startFieldset('Add New User to Project');

$form->setCols(0,5);
$form->groupInputs('add_username', 'add_access_level', 'submit-btn');
$form->addHelper('Username', 'add_username');
$form->addInput('text', 'add_username', '', '', '');

$form->setCols(0,2);
$form->addHelper('Access Level', 'add_access_level');
$form->addOption('add_access_level', 'visitor', 'Visitor', '', '');
$form->addOption('add_access_level', 'collaborator', 'Collaborator', '', '');
$form->addOption('add_access_level', 'admin', 'Admin', '', '');
$form->addSelect('add_access_level', '', '');

$form->setCols(0,5);
$form->addBtn('submit', 'submit-btn', "add-user", '<i class="fa fa-plus" aria-hidden="true"></i> Add New User', 'class=btn btn-success ladda-button, data-style=zoom-in', 'add-group');
$form->printBtnGroup('add-group');

$form->endFieldset();

$form->startFieldset('Edit Current Users');

$db = new Mysql();
$filter = array("project_id"=> Mysql::SQLValue($_GET['project_id']));
$db->selectRows($table_name, $filter);
foreach ($db->recordsArray() as $user) {
    $username_input = 'username,'.$user["username"];
    $access_input = 'access_level,'.$user["username"];
    $form->startFieldset('');

    $form->setCols(0,5);
    $form->groupInputs($username_input, $access_input, "submit-btn");
    $form->addHelper('Username', $username_input);
    $form->addInput('text', $username_input, $user['username'], '', 'readonly="readonly"');

    $form->setCols(0,2);
    $_SESSION[$form_name][$access_input] = $user['access_level']; // Fill in access level from db
    $form->addHelper('Access Level', $access_input);
    $form->addOption($access_input, 'visitor', 'Visitor', '', '');
    $form->addOption($access_input, 'collaborator', 'Collaborator', '', '');
    $form->addOption($access_input, 'admin', 'Admin', '', '');
    $form->addSelect($access_input, '', '');


    $form->setCols(0,5);
    $form->addBtn('submit', 'submit-btn', $user['username'], '<i class="fa fa-trash" aria-hidden="true"></i> Remove User', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to remove this user from your project?\')', 'delete-btn-'.$user["username"]);
    $form->printBtnGroup('delete-btn-'.$user["username"]);

    $form->endFieldset();
}

$form->endFieldset();
$form->startFieldset('');
$form->setCols(0,12);
$form->addHtml("<br>");
$form->setOptions(array('buttonWrapper'=>'<div class="form-group row justify-content-end"></div>'));
$form->addBtn('submit', 'submit-btn', "save", '<i class="fa fa-save" aria-hidden="true"></i> Save Changes', 'class=btn btn-success ladda-button, data-style=zoom-in', 'save-group');
$form->addBtn('reset', 'reset-btn', 1, '<i class="fa fa-ban" aria-hidden="true"></i> Reset', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'save-group');
$form->printBtnGroup('save-group');

$form->endFieldset();

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($_GET["project_id"]." > Edit User Access");
$page_render->renderPage();