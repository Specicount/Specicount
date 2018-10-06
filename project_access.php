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
    protected function setRequiredAccessLevelsForPost() {
        // Any member can leave a project
        $this->post_required_access_levels = array("owner","admin","collaborator", "visitor");
    }

    protected function registerPostActions() {
        $this->registerPostAction("addUser", isset($_POST['submit-btn']) && $_POST['submit-btn'] == "add-new-user");
        $this->registerPostAction("saveChanges", isset($_POST['submit-btn']) && $_POST['submit-btn'] == "save-multiple");
        $this->registerPostAction("deleteUser", isset($_POST["delete-btn"]));
        $this->registerPostAction("leave", isset($_POST['leave-btn']));
        $this->registerPostAction("leaveAsOwner", isset($_POST['leave-owner-btn']));
    }

    protected function addUser() {
        // -------- VALIDATION --------
        $my_access_level = getAccessLevel();
        if ($my_access_level == "visitor") {
            storeErrorMsg("You cannot add new users as a visitor");
            return;
        }
        if ($_POST["new_access_level"] == "owner") {
            storeErrorMsg("There can only be one owner of a project");
            return;
        }
        if ($_POST["new_access_level"] == "admin" && $my_access_level != "owner") {
            storeErrorMsg("You have to be the owner to add new admins to the project");
            return;
        }

        // -------- ADD USER --------
        $update["project_id"] = Mysql::sqlValue($_GET["project_id"]);
        $update["email"] = Mysql::sqlValue($_POST["new_email"]);
        $update["access_level"] = Mysql::sqlValue($_POST["new_access_level"]);
        $this->db->insertRow($this->table_name, $update);
        storeDbMsg($this->db,"Successfully added " . $update["email"] . " to the project!", "User already added or does not exist");
    }

    protected function deleteUser() {
        // ---- VALIDATION ----
        $my_access_level = getAccessLevel();
        if ($my_access_level == "visitor") {
            storeErrorMsg("You cannot delete users as a visitor");
            return;
        }
        $deleted_user_email = $_POST["delete-btn"];
        $deleted_user_access_level = getAccessLevel($deleted_user_email);
        // Make sure the owner cannot be deleted
        if ($deleted_user_access_level == "owner") {
            storeErrorMsg("You cannot delete the owner");
            return;
        }
        // Make sure an admin cannot delete other admins
        if ($deleted_user_access_level == "admin" && $my_access_level == "admin") {
            storeErrorMsg("You cannot delete other admins");
            return;
        }

        // -------- DELETE --------
        $email = $_POST["delete-btn"];
        $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
        $filter["email"] = Mysql::SQLValue($email);
        $this->db->deleteRows($this->table_name, $filter);
        storeDbMsg($this->db,"Successfully deleted " . $email . " from access list!");
    }

    // Update all users access levels
    protected function saveChanges() {
        // -------- VALIDATION --------
        $my_access_level = getAccessLevel();
        if ($my_access_level == "visitor") {
            storeErrorMsg("You cannot make changes as a visitor");
            return;
        }
        foreach($_POST["access_level"] as $key => $value) {
            $access_levels[$key] = lcfirst($value); // This array is needed for readonly inputs
        }
        $num_users = count($_POST["email"]);
        for ($i=0; $i<$num_users; $i++) {
            $new_access_level[$_POST["email"][$i]] = $access_levels[$i];
        }
        $filter["project_id"] = Mysql::sqlValue($_GET["project_id"]);
        $this->db->selectRows($this->table_name, $filter);
        $db_users = $this->db->recordsArray();
        $my_access_level = getAccessLevel();
        foreach ($db_users as $db_user) {
            $email = $db_user["email"];
            $old_access_level = $db_user["access_level"];
            // Make sure that nobody changes the access level of the owner
            if ($old_access_level == "owner" && $old_access_level != $new_access_level[$email]) {
                storeErrorMsg("You cannot change the access level of the owner");
                return;
            }
            // Make sure no user is ever given the owner access level, unless they are already the owner
            if ($old_access_level != "owner" && $new_access_level[$email] == "owner") {
                storeErrorMsg("There can only be one owner of a project");
                return;
            }
            // Make sure only the owner can change the access level of admins
            if ($old_access_level == "admin" && $old_access_level != $new_access_level[$email] && $my_access_level != "owner") {
                storeErrorMsg("Only the owner can change the access level of other admins");
                return;
            }
            // Make sure only the owner can upgrade non-admins to admins
            if ($old_access_level != "admin" && $new_access_level[$email] == "admin" && $my_access_level != "owner") {
                storeErrorMsg("Only the owner can upgrade non-admins to admins");
                return;
            }

        }

        // -------- SAVE CHANGES ------------
        for ($i=0; $i<$num_users; $i++) {
            $update["access_level"] = Mysql::SQLValue($_POST["access_level"][$i]);
            $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
            $filter["email"] = Mysql::SQLValue($_POST["email"][$i]);
            $this->db->updateRows($this->table_name, $update, $filter);
        }
        storeDbMsg($this->db,"Changes have been saved!");
    }

    protected function leave() {
        // -------- VALIDATION --------
        $my_access_level = getAccessLevel();
        if ($my_access_level == "owner") {
            storeErrorMsg("You must transfer ownership to another user before you leave!");
            return;
        }

        // -------- LEAVE --------
        $filter["email"] = Mysql::sqlValue($_SESSION["email"]);
        $filter["project_id"] = Mysql::sqlValue($_GET["project_id"]);
        $this->db->deleteRows($this->table_name, $filter);
        storeDbMsg($this->db,"You have successfully left the project");

        header("location: projects.php");
    }

    protected function leaveAsOwner() {
        // -------- VALIDATION --------
        $my_access_level = getAccessLevel();
        // Make sure that only the owner can transfer ownership
        if ($my_access_level != "owner") {
            storeErrorMsg("You must be the owner to do that");
            return;
        }
        // Make sure that the new owner exists
        $filter["email"] = Mysql::sqlValue($_POST["new-owner-email"]);
        $this->db->selectRows("users", $filter);
        if ($this->db->rowCount() == 0) {
            storeErrorMsg("User ".$filter["email"]." does not exist");
            return;
        }

        // -------- LEAVE AS OWNER --------
        $filter["project_id"] = Mysql::sqlValue($_GET["project_id"]);
        $update = $filter;
        $update["access_level"] = Mysql::sqlValue("owner");
        $this->db->autoInsertUpdate($this->table_name, $filter, $update);
        storeDbMsg($this->db);

        // Delete old owner from access list
        $filter["email"] = Mysql::sqlValue($_SESSION["email"]);
        $this->db->deleteRows($this->table_name, $filter);
        storeDbMsg($this->db,"Successfully transferred ownership!");

        header("location: projects.php");
    }
}

/* ==================================================
    The Form
================================================== */
$form = new Access_Form("project-access", "user_project_access", 'horizontal', 'novalidate', 'bs4');
Form::clear($form->getFormName());
$form->setOptions(array('buttonWrapper'=>'')); // So that the button can be printed on the same row as the other inputs

$my_access_level = getAccessLevel();

if ($my_access_level != "visitor") {
    $form->startFieldset('Add New User to Project');

    $form->setCols(0,6);
    $form->groupInputs('new_email', 'new_access_level', 'submit-btn');
    $form->addHelper('Email', 'new_email');
    $form->addInput('text', 'new_email');

    $form->setCols(0,2);
    $form->addHelper('Access Level', 'new_access_level');
    $form->addOption('new_access_level', 'visitor', 'Visitor');
    $form->addOption('new_access_level', 'collaborator', 'Collaborator');
    if ($my_access_level == "owner") {
        $form->addOption('new_access_level', 'admin', 'Admin');
    }
    $form->addSelect('new_access_level');

    $form->setCols(0,4);
    $form->addBtn('submit', 'submit-btn', "add-new-user", '<i class="fa fa-plus" aria-hidden="true"></i> Add New User', 'class=btn btn-success ladda-button, data-style=zoom-in', 'add-group');
    $form->printBtnGroup('add-group');

    $form->endFieldset();
    $form->startFieldset('Edit Current Users');
} else {
    $form->startFieldset('Users');
}




// These options and helper texts only need to be added once, otherwise they duplicate due to each input having the same name (e.g. email[])
$form->addOption("access_level[]", 'visitor', 'Visitor');
$form->addOption("access_level[]", 'collaborator', 'Collaborator');
if ($my_access_level == "owner") {
    $form->addOption("access_level[]", 'admin', 'Admin');
}
$form->addHelper('First Name', 'first_name[]');
$form->addHelper('Last Name', 'last_name[]');
$form->addHelper('Access Level', 'access_level[]');
$form->setOptions(array('elementsWrapper'=>'')); // So that the select inputs can be printed on the same row as the other inputs

$db = new Mysql();
$filter = array();
$filter["project_id"] = Mysql::sqlValue($_GET["project_id"]);
$sql =  "SELECT email, first_name, last_name, access_level FROM users NATURAL JOIN user_project_access ".
        Mysql::buildSQLWhereClause($filter).
        " ORDER BY access_level";
$db->query($sql);
foreach ($db->recordsArray() as $user) {
    $form->addHtml('<div class="form-group row justify-content-end">');
    $form->setCols(0,3);
    $form->groupInputs("email[]", "first_name[]", "last_name[]", "delete-btn[]");
    $form->addInput("hidden", "email[]", $user["email"]);
    $form->addInput("text", "first_name[]", $user["first_name"], '', 'readonly="readonly"');
    $form->addInput("text", "last_name[]", $user["last_name"], '', 'readonly="readonly"');

    $form->setCols(0,2);
    $_SESSION[$form->getFormName()]["access_level"] = $user['access_level']; // Fill in access level from db
    if ($user["access_level"] == "owner") {
        // Always grey out the owner
        $form->addInput("text", "access_level[]", ucwords($user["access_level"]), '', 'readonly="readonly"');
        $form->addHtml('<div class="col-sm-4"></div>'); // Space filler for where the delete button would be
    } else if ($my_access_level == "owner") {
        // Give me the ability to delete or change the access level of any other user
        $form->addSelect("access_level[]");
        $form->setCols(0,4);
        $form->addBtn('submit', 'delete-btn', $user['email'], '<i class="fa fa-trash" aria-hidden="true"></i> Remove User', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to remove this user from your project?\')', 'delete-btn-'.$user["email"]);
        $form->printBtnGroup('delete-btn-'.$user["email"]);
    } else if ($my_access_level == "admin") {
        if ($user["access_level"] == "admin") {
            // Don't allow admins to change/delete other admins
            $form->addInput("text", "access_level[]", ucwords($user["access_level"]), '', 'readonly="readonly"');
            $form->addHtml('<div class="col-sm-4"></div>'); // Space filler for where the delete button would be
        } else {
            // Allow admins to change/delete other users
            $form->addSelect("access_level[]");
            $form->setCols(0,4);
            $form->addBtn('submit', 'delete-btn', $user['email'], '<i class="fa fa-trash" aria-hidden="true"></i> Remove User', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to remove this user from your project?\')', 'delete-btn-'.$user["email"]);
            $form->printBtnGroup('delete-btn-'.$user["email"]);
        }
    } else {
        // If you not an admin or owner, then grey out other users
        $form->addInput("text", "access_level[]", ucwords($user["access_level"]), '', 'readonly="readonly"');
        $form->addHtml('<div class="col-sm-4"></div>'); // Space filler for where the delete button would be
    }

    $form->addHtml('</div>');
}
$form->endFieldset();

if ($my_access_level != "visitor") {
    $form->startFieldset('');
    $form->setCols(0,12);
    $form->addHtml("<br>");
    $form->setOptions(array('buttonWrapper'=>'<div class="form-group row justify-content-end"></div>'));
    $form->addBtn('submit', 'submit-btn', "save-multiple", '<i class="fa fa-save" aria-hidden="true"></i> Save Changes', 'class=btn btn-success ladda-button, data-style=zoom-in', 'save-group');
    $form->printBtnGroup('save-group');
    $form->endFieldset();
}

$form->setOptions(array('buttonWrapper'=>'')); // So that the leave button can be printed on the same row as the input
if ($my_access_level == "owner") {
    $form->startFieldset("Leave project ".$_GET["project_id"]." and transfer ownership", "style=margin-top:80px;");
    $form->addHtml('<div class="form-group row">');
    $form->groupInputs("new-owner-email","leave-btn");
    $form->setCols(0,5);
    $form->addHelper("New Owner's Email", "new-owner-email");
    $form->addInput("text","new-owner-email", "", "", "required");
    $form->setCols(0,7);
    $form->addBtn('submit', 'leave-owner-btn', true, '<i class="fa fa-sign-out-alt" aria-hidden="true"></i> Leave and Transfer Ownership', "class=btn btn-warning, onclick=return confirm('Are you sure you want to leave this project and transfer ownership?')");
} else {
    unset($_SESSION[$form->getFormName()]["required_fields"]);
    $form->startFieldset("Leave project '".$_GET["project_id"]."'", "style=margin-top:80px;");
    $form->addHtml('<div class="form-group row">');
    $form->setCols(0,12);
    $form->addBtn('submit', 'leave-btn', true, '<i class="fa fa-sign-out-alt" aria-hidden="true"></i> Leave This Project', "class=btn btn-warning, onclick=return confirm('Are you sure you want to leave this project?')");
}
$form->addHtml('</div>');
$form->endFieldset();



// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($_GET["project_id"]." > Edit User Access");
$page_render->setPageRestrictions(true, true, false, false);
$page_render->renderPage();