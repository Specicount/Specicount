<?php
/**
 * Created by IntelliJ IDEA.
 * User: Elliott
 * Date: 23/09/2018
 * Time: 8:57 AM
 */

use phpformbuilder\Form;
use phpformbuilder\database\Mysql;
use classes\Post_Form;
use function functions\getTopMostScript;
use function functions\getAccessLevel;
use function functions\storeErrorMsg;
use function functions\storeSuccessMsg;
use function functions\storeDbMsg;

require_once "classes/Page_Renderer.php";
require_once "classes/Post_Form.php";

class Leave_Form extends Post_Form {

    protected function setRequiredAccessLevelsForPost() {
        // Any member can leave a project
        $this->post_required_access_levels = array("owner","admin","collaborator", "visitor");
    }

    protected function registerPostActions() {
        $this->registerPostAction("leave", isset($_POST['leave-btn']));
        $this->registerPostAction("leaveAsOwner", isset($_POST['leave-owner-btn']));
    }

    protected function additionalValidation() {
        $my_access_level = getAccessLevel();
        if (!$my_access_level) {
            storeDbMsg($this->db,"You must be a member of the project before you can leave it!");
            return false;
        }
        return true;
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
        $this->db->selectRows("user", $filter);
        if ($this->db->rowCount() == 0) {
            storeErrorMsg("That user does not exist");
            return;
        }

        // -------- LEAVE AS OWNER --------
        //TODO: autoinsertupdate
        $filter["project_id"] = Mysql::sqlValue($_GET["project_id"]);
        $this->db->selectRows($this->table_name, $filter);
        // If new owner is already in the project
        if ($this->db->rowCount() > 0) {
            // Upgrade their access level to owner
            $update["access_level"] = Mysql::sqlValue("owner");
            $this->db->updateRows($this->table_name, $filter, $update);
        } else {
            // Otherwise add them to the access list as owner
            $update = $filter;
            $update["access_level"] = Mysql::sqlValue("owner");
            $this->db->insertRow($this->table_name, $update);
        }
        storeDbMsg($this->db);

        // Delete old owner from access list
        $filter["email"] = Mysql::sqlValue($_SESSION["email"]);
        $this->db->deleteRows($this->table_name, $filter);
        storeDbMsg($this->db,"Successfully transferred ownership!");
    }


}

$form = new Leave_Form("leave", "user_project_access", 'horizontal', 'novalidate', 'bs4');

$my_access_level = getAccessLevel();
if ($my_access_level == "owner") {
    $form->startFieldset("You must transfer ownership before you can leave ".$_GET["project_id"]."");
    $form->groupInputs("new-owner-email","leave-btn");
    $form->setCols(0,5);
    $form->addHelper("New Owner's Email", "new-owner-email");
    $form->addInput("text","new-owner-email", "", "", "required");
    $form->setCols(0,7);
    $form->setOptions(array('buttonWrapper'=>'')); // So that the button can be printed on the same row as the other inputs
    $form->addBtn('submit', 'leave-owner-btn', true, "Leave and Transfer Ownership", "class=btn btn-success, onclick=return confirm('Are you sure you want to leave this project and transfer ownership?')", 'btn-group');
} else {
    $form->startFieldset("Are you sure you want to leave ".$_GET["project_id"]."?");
    $form->setCols(0,12);
    $form->addBtn('submit', 'leave-btn', true, "Yes I'm sure", "class=btn btn-success, onclick=return confirm('Are you sure you want to leave this project?')", 'btn-group');
}

$form->printBtnGroup("btn-group");
$form->endFieldset();

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle("Leave Project");
$page_render->renderPage();

