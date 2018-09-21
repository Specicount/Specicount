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
$form->groupInputs('username', 'access_level', 'submit-btn');
$form->addHelper('Username', 'username');
$form->addInput('text', 'username', '', '', '');

$form->setCols(0,2);
$form->addHelper('Access Level', 'access_level');
$form->addOption('access_level', 'visitor', 'Visitor', '', '');
$form->addOption('access_level', 'collaborator', 'Collaborator', '', '');
$form->addOption('access_level', 'admin', 'Admin', '', '');
$form->addSelect('access_level', '', '');

$form->setCols(0,5);
$form->addBtn('submit', 'submit-btn', "save", '<i class="fa fa-plus" aria-hidden="true"></i> Add New User', 'class=btn btn-success ladda-button, data-style=zoom-in', 'add-group');
$form->printBtnGroup('add-group');

$form->endFieldset();

$form->startFieldset('Edit Current Users');

$filter = $form->getFilterArray();
$db = new Mysql();
$db->selectRows($form->getTableName(), $filter);
foreach ($db->recordsArray() as $user) {
    $username_input = 'username,'.$user["username"];
    $access_input = 'access_level,'.$user["username"];
    $form->startFieldset('');

    $form->setCols(0,5);
    $form->groupInputs($username_input, $access_input, "submit-btn");
    $form->addHelper('Username', $username_input);
    $form->addInput('text', $username_input, $user['username'], '', 'readonly="readonly"');

    $form->setCols(0,2);
    $_SESSION[$form->getFormName()][$access_input] = $user['access_level']; // Fill in access level from db
    $form->addHelper('Access Level', $access_input);
    $form->addOption($access_input, 'visitor', 'Visitor', '', '');
    $form->addOption($access_input, 'collaborator', 'Collaborator', '', '');
    $form->addOption($access_input, 'admin', 'Admin', '', '');
    $form->addSelect($access_input, '', '');


    $form->setCols(0,5);
    $form->addBtn('submit', 'delete-btn', $user['username'], '<i class="fa fa-trash" aria-hidden="true"></i> Remove User', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to remove this user from your project?\')', 'delete-btn-'.$user["username"]);
    $form->printBtnGroup('delete-btn-'.$user["username"]);

    $form->endFieldset();
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