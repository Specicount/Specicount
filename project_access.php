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

$form->setCols(0, 12);

$form->addHelper('Username', 'username');
$form->addInput('text', 'username', '', '', 'required, class=col-4');

$form->addHelper('First Name', 'first_name');
$form->addInput('text', 'first_name', '', '', 'required, class=col-4');

$form->addHelper('Last Name', 'last_name');
$form->addInput('text', 'last_name', '', '', 'required, class=col-4');

$form->addHelper('Email', 'email');
$form->addInput('email', 'email', '', '', 'required, placeholder=Email, class=col-4');

$form->addHelper('Your Institution/Company', 'institution');
$form->addInput('text', 'institution', '', '', "class=col-4");