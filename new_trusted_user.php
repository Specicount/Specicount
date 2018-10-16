<?php
/**
 * Created by IntelliJ IDEA.
 * User: Elliott
 * Date: 16/10/2018
 * Time: 5:27 PM
 */

use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;
use classes\Post_Form;

require_once "classes/Page_Renderer.php";
require_once "classes/Post_Form.php";

class Trusted_User_Form extends Post_Form {
    protected function registerPostActions() {
        $this->registerPostAction("makeUserTrusted", isset($_POST['submit-btn']));
    }

    protected function makeUserTrusted() {
        // -------- VALIDATION --------

        // Make sure that only trusted users can make other users trusted
        $filter = array();
        $filter["email"] = Mysql::sqlValue($_SESSION["email"]);
        $this->db->selectRows("users", $filter);
        if ($this->db->recordsArray()[0]["is_trusted"] == false) {
            storeErrorMsg("You must be a trusted user to make other users trusted");
            return;
        }

        // Make sure that the specified user exists
        $filter = array();
        $filter["email"] = Mysql::sqlValue($_POST["new-trusted-user-email"]);
        $this->db->selectRows("users", $filter);
        if ($this->db->rowCount() == 0) {
            storeErrorMsg("User " . $filter["email"] . " does not exist");
            return;
        }

        // -------- MAKE USER TRUSTED --------

        // Make the specified user trusted
        $filter = $update = array();
        $filter["email"] = Mysql::sqlValue($_POST["new-trusted-user-email"]);
        $update["is_trusted"] = Mysql::sqlValue('TRUE','boolean');
        $this->db->updateRows('users', $update, $filter);

        // Add the specified user to the Global Reference Specimens project
        $update = array();
        $update["email"] = Mysql::sqlValue($_POST["new-trusted-user-email"]);
        $update["project_id"] = Mysql::sqlValue("Global Reference Specimens");
        $update["access_level"] = Mysql::sqlValue("collaborator");
        $this->db->insertRow('user_project_access',$update);
        storeDbMsg($this->db,'',"User already added to 'Global Reference Specimens' project");

        storeSuccessMsg("Successfully made ".$update["email"]." a trusted user and added them to the 'Global Reference Specimens' project!");
    }
}

$form = new Trusted_User_Form("new-trusted-user", "users", 'horizontal', 'novalidate', 'bs4');
$form->startFieldset("Make another user trusted");
$form->groupInputs("new-trusted-user-email", "submit-btn");
$form->setCols(0,4);
$form->addHelper('User Email','new-trusted-user-email');
$form->addInput('text','new-trusted-user-email','','','required');
$form->setCols(0,8);
$form->addBtn('submit','submit-btn','','<i class="fa fa-user-plus" aria-hidden="true"></i> Make User Trusted', 'class=btn btn-success ladda-button, data-style=zoom-in');
$form->endFieldset();


$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle("Add new trusted user");
$page_render->setPageRestrictions(true);
$page_render->disableSidebar();
$page_render->renderPage();