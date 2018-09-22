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
        $this->registerPostAction("create", isset($_POST['submit-btn']) && $_POST['submit-btn'] == "add-new-user");
        $this->registerPostAction("update", isset($_POST['submit-btn']) && $_POST['submit-btn'] == "save-multiple");
        $this->registerPostAction("delete", isset($_POST["delete-btn"]), false);
    }

    protected function create() {
        $update["project_id"] = Mysql::sqlValue($_GET["project_id"]);
        $update["email"] = Mysql::sqlValue($_POST["new_email"]);
        $update["access_level"] = Mysql::sqlValue($_POST["new_access_level"]);
        $this->db->insertRow($this->table_name, $update);
        $this->storeDbMsg("Successfully added " . $update["email"] . " to the project!", "User already added or does not exist");
    }

    protected function delete() {
        $email = $_POST["delete-btn"];
        $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
        $filter["email"] = Mysql::SQLValue($email);
        $this->db->deleteRows($this->table_name, $filter);
        $this->storeDbMsg("Successfully deleted " . $email . " from access list!");
    }

    // Update all users access levels
    protected function update() {
        $num_users = count($_POST["email"]);
        for ($i=0; $i<$num_users; $i++) {
            $update["access_level"] = Mysql::SQLValue($_POST["access_level"][$i]);
            $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
            $filter["email"] = Mysql::SQLValue($_POST["email"][$i]);
            $this->db->updateRows($this->table_name, $update, $filter);
        }
        $this->storeDbMsg("Changes have been saved!");
    }
}

/* ==================================================
    The Form
================================================== */
$form = new Access_Form("project-access", "user_project_access", 'horizontal', 'novalidate', 'bs4');
Form::clear($form->getFormName());
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
$form->addBtn('submit', 'submit-btn', "add-new-user", '<i class="fa fa-plus" aria-hidden="true"></i> Add New User', 'class=btn btn-success ladda-button, data-style=zoom-in', 'add-group');
$form->printBtnGroup('add-group');

$form->endFieldset();

$form->startFieldset('Edit Current Users');

$filter = $form->getFilterArray();
$db = new Mysql();
$sql =  "SELECT email, first_name, last_name, access_level FROM users NATURAL JOIN user_project_access ".
        Mysql::buildSQLWhereClause($filter).
        " ORDER BY access_level";
$db->query($sql);

$form->addOption("access_level[]", 'visitor', 'Visitor');
$form->addOption("access_level[]", 'collaborator', 'Collaborator');
$form->addOption("access_level[]", 'admin', 'Admin');
$i = 0;


foreach ($db->recordsArray() as $user) {
    $form->startFieldset('');
    $form->addHtml('<div class="form-group row justify-content-end">');
    $form->setCols(0,3);
    $form->groupInputs("email[]", "first_name[]", "last_name[]", "delete-btn[]");
    $form->addInput("hidden", "email[]", $user["email"]);
    $form->addInput("text", "first_name[]", $user["first_name"], '', 'readonly="readonly"');
    $form->addInput("text", "last_name[]", $user["last_name"], '', 'readonly="readonly"');

    $form->setCols(0,2);
    $form->setOptions(array('elementsWrapper'=>''));
    $_SESSION[$form->getFormName()]["access_level"][$i] = $user['access_level']; // Fill in access level from db
    if ($user["access_level"] == "owner") {
        $form->addInput("text", "access_level[]", ucwords($user["access_level"]), '', 'readonly="readonly"');
    } else {
        $form->addSelect("access_level[]");
    }

    $form->setOptions(array('elementsWrapper'=>'<div class="form-group"></div>'));
    $form->setCols(0,4);
    $form->addBtn('submit', 'delete-btn', $user['email'], '<i class="fa fa-trash" aria-hidden="true"></i> Remove User', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to remove this user from your project?\')', 'delete-btn-'.$user["email"]);
    $form->printBtnGroup('delete-btn-'.$user["email"]);
    $form->addHtml('</div>');
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