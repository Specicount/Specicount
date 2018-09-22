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
use classes\Post_Form;

require_once "classes/Page_Renderer.php";
require_once "classes/Post_Form.php";

//$db = new Mysql();
//$form_ID = "project-access";
//$table_name = "user_project_access";
//unset($_SESSION[$form_ID]); // To ensure that other users aren't accidentally added as admin

class Access_Form extends Post_Form {
    protected function registerPostActions() {
        parent::registerPostActions();
        $this->registerPostAction("update", isset($_POST['submit-btn']) && $_POST['submit-btn'] == "save-multiple");
        $this->registerPostAction("delete", isset($_POST["delete-btn"]), false);
    }

    protected function create() {
        $this->db->insertRow($this->table_name, $this->update);
        $this->storeDbMsg("Successfully added " . $this->update["username"] . " to the project!", "User already added or does not exist");
    }

    protected function delete() {
        $username = $_POST["delete-btn"];
        $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
        $filter["username"] = Mysql::SQLValue($username);
        $this->db->deleteRows($this->table_name, $filter);
        $this->storeDbMsg("Successfully deleted " . $username . " from access list!");
    }

    // Update all users access levels
    protected function update() {
        foreach (array_keys($_POST) as $key) {
            if (strpos($key, "access_level") !== false) {
                // TODO: May cause problems if usernames can have commas
                $username = explode(',', $key)[1];
                $access_level = $_POST[$key];

                $update["project_id"] = Mysql::SQLValue($_GET["project_id"]);
                $update["username"] = Mysql::SQLValue($username);
                $update["access_level"] = Mysql::SQLValue($access_level);

                $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
                $filter["username"] = Mysql::SQLValue($username);

                $this->db->updateRows($this->table_name, $update, $filter);
            }
        }
        $this->storeDbMsg("Changes have been saved!");
    }
}

/* ==================================================
    The Form
================================================== */

$form = new Access_Form("project-access", "user_project_access", 'horizontal', 'novalidate', 'bs4');
$form->setOptions(array('buttonWrapper'=>'')); // So that the button can be printed on the same row as the other inputs

$form->startFieldset('Add New User to Project');

$form->setCols(0,5);
$form->groupInputs('new_email', 'new_access_level', 'submit-btn');
$form->addHelper('Email', 'new_email');
$form->addInput('text', 'new_email');

$form->setCols(0,2);
$form->addHelper('Access Level', 'new_access_level');
$form->addOption('new_access_level', 'visitor', 'Visitor');
$form->addOption('new_access_level', 'collaborator', 'Collaborator');
$form->addOption('new_access_level', 'admin', 'Admin');
$form->addSelect('new_access_level');

$form->setCols(0,5);
$form->addBtn('submit', 'submit-btn', "save", '<i class="fa fa-plus" aria-hidden="true"></i> Add New User', 'class=btn btn-success ladda-button, data-style=zoom-in', 'add-group');
$form->printBtnGroup('add-group');

$form->endFieldset();

$form->startFieldset('Edit Current Users');

$filter = $form->getFilterArray();
$db = new Mysql();
$sql =  "SELECT email, first_name, last_name, access_level FROM users NATURAL JOIN user_project_access ".
        Mysql::buildSQLWhereClause($filter).
        " ORDER BY access_level";
$db->query($sql);
$i = 0;
print_r($db->error());
print_r($db->recordsArray());
print_r($_POST);
foreach ($db->recordsArray() as $user) {
    $form->startFieldset('');


    $form->setCols(0,3);
    $form->groupInputs("email[]", "first_name[]", "last_name[]", "submit-btn");
    $form->addInput("hidden", "email[]", $user["email"]);
    $form->addInput("text", "first_name[]", $user["first_name"], '', 'readonly="readonly"');
    $form->addInput("text", "last_name[]", $user["last_name"], '', 'readonly="readonly"');
    $form->addHelper('Access Level', "access_level[]");

    $form->setCols(0,2);
    if ($user["access_level"] === "owner") {
        $form->addInput("text", "access_level[]", ucwords($user["access_level"]), '', 'readonly="readonly"');
    } else {
        $form->addOption("access_level[]", 'visitor', 'Visitor', '', '');
        $form->addOption("access_level[]", 'collaborator', 'Collaborator', '', '');
        $form->addOption("access_level[]", 'admin', 'Admin', '', '');
        $form->addSelect("access_level[]");
    }



//
//    $form->addInput("text", $email_input, $user["email"]);
//    $form->addHelper('Username', $username_input);
//    $form->addInput('text', $username_input, $user['username'], '', 'readonly="readonly"');
//
//    $form->setCols(0,2);
//    $_SESSION[$form->getFormName()]["access_level"][$i] = $user['access_level']; // Fill in access level from db
//    $form->addHelper('Access Level', $access_input);
//    $form->addOption($access_input, 'visitor', 'Visitor', '', '');
//    $form->addOption($access_input, 'collaborator', 'Collaborator', '', '');
//    $form->addOption($access_input, 'admin', 'Admin', '', '');
//    $form->addSelect($access_input, '', '');


    $form->setCols(0,4);
    $form->addBtn('submit', 'delete-btn[]', $user['email'], '<i class="fa fa-trash" aria-hidden="true"></i> Remove User', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to remove this user from your project?\')', 'delete-btn-'.$user["email"]);
    $form->printBtnGroup('delete-btn-'.$user["email"]);

    $form->endFieldset();
    $i++;
}

$form->endFieldset();
$form->startFieldset('');
$form->setCols(0,12);
$form->addHtml("<br>");
$form->setOptions(array('buttonWrapper'=>'<div class="form-group row justify-content-end"></div>'));
$form->addBtn('submit', 'submit-btn', "save-multiple", '<i class="fa fa-save" aria-hidden="true"></i> Save Changes', 'class=btn btn-success ladda-button, data-style=zoom-in', 'save-group');
$form->addBtn('reset', 'reset-btn', 1, '<i class="fa fa-ban" aria-hidden="true"></i> Reset', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'save-group');
$form->printBtnGroup('save-group');

$form->endFieldset();

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($_GET["project_id"]." > Edit User Access");
$page_render->renderPage();