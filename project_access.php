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
use classes\Abstract_Form;

require_once "classes/Page_Renderer.php";
require_once "classes/Abstract_Form.php";

class Project_Access_Form extends Abstract_Form {
    public function setFormType() {
        $this->form_type = "project_access";
    }

    public function setSqlTableName() {
        $this->table_name = "user_project_access";
    }
}

$project_access_form = new Project_Access_Form();

$form = new Form($project_access_form->getFormName(), 'horizontal', 'novalidate', 'bs4');
$form->setOptions(array('buttonWrapper'=>''));



$form->startFieldset('Add New User to Project');

$form->setCols(0,5);
$form->groupInputs('add_username', 'add_access_level', 'add-new-user-btn');
$form->addHelper('Username', 'add_username');
$form->addInput('text', 'add_username', '', '', 'required');

$form->setCols(0,2);
$form->addHelper('Access Level', 'access_level');
$form->addOption('add_access_level', 'visitor', 'Visitor', '', '');
$form->addOption('add_access_level', 'collaborator', 'Collaborator', '', '');
$form->addOption('add_access_level', 'admin', 'Admin', '', '');
$form->addSelect('add_access_level', '', 'required');

$form->setCols(0,5);
$form->addBtn('submit', 'add-new-user-btn', "add-new-user", '<i class="fa fa-plus" aria-hidden="true"></i> Add New User', 'class=btn btn-success ladda-button, data-style=zoom-in', 'add-group');
$form->printBtnGroup('add-group');

$form->endFieldset();


$form->startFieldset('Edit Current Users');

$db = new Mysql();
$filter = array("project_id"=> Mysql::SQLValue($_GET['project_id']));
$db->selectRows($project_access_form->getTableName(), $filter);
foreach ($db->recordsArray() as $user) {
    $form->setCols(0,5);
    $form->groupInputs('username,'.$user['username'], 'access_level,'.$user['username'], 'add-new-user-btn,'.$user['username']);
    $form->addHelper('Username', 'username,'.$user['username']);
    $form->addInput('text', 'username,'.$user['username'], '', '', 'required');

    $form->setCols(0,2);
    $form->addHelper('Access Level', 'access_level');
    $form->addOption('access_level,'.$user['username'], 'visitor', 'Visitor', '', '');
    $form->addOption('access_level,'.$user['username'], 'collaborator', 'Collaborator', '', '');
    $form->addOption('access_level,'.$user['username'], 'admin', 'Admin', '', '');
    $form->addSelect('access_level,'.$user['username'], '', 'required');

    $form->setCols(0,5);
    $form->addBtn('submit', 'delete-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to remove this user from your project?\')', 'delete-btn-group');
    $form->printBtnGroup('my-btn-group');
}
$form->endFieldset();

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($project_access_form->getPageTitle());
$page_render->renderPage();