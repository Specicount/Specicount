<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

require_once "classes/Page_Renderer.php";
require_once "classes/Abstract_Form.php";


class Project_Form extends \classes\Abstract_Form {
    public function getFormType() {
        return "project";
    }

    public function getTableName() {
        return "projects";
    }

    public function delete($db, $filter) {
        $db->deleteRows('projects', array("project_name" => Mysql::SQLValue($_POST["project_name"],text)));
    }

    public function submit($db, $update) {
        $db->insertRow($this->getTableName(), $update);
    }

    public function update($db, $update, $filter) {
        $db->updateRows($this->getTableName(), $update, $filter);
    }
}



$project_form = new Project_Form();

/* ==================================================
    The Form
================================================== */

$form = new Form($project_form->getFormName(), 'horizontal', 'novalidate', 'bs4');


$form->startFieldset('Project Data');
$form->setCols(6, 6);
$form->groupInputs('project_name', 'biorealm');

$form->addHelper('Project Name', 'project_name');
if ($_GET["edit"]) {
    $form->addInput('text', 'project_name', '', '', 'required, readonly="readonly"');
} else {
    $form->addInput('text', 'project_name', '', '', 'required'); // Need to have warning for code
}
$form->setCols(0, 6);

$form->addHelper('Biorealm', 'biorealm');
$form->addOption('biorealm', '',  'Choose one ...', '', 'disabled, selected');
$form->addOption('biorealm', 'Nearctic',  'Nearctic', '', '');
$form->addOption('biorealm', 'Palearctic',  'Palearctic', '', '');
$form->addOption('biorealm', 'Afrotropic',  'Afrotropic', '', '');
$form->addOption('biorealm', 'Indomalaya',  'Indomalaya', '', '');
$form->addOption('biorealm', 'Australasia',  'Australasia', '', '');
$form->addOption('biorealm', 'Neotropic',  'Neotropic', '', '');
$form->addOption('biorealm', 'Oceania',  'Oceania', '', '');
$form->addOption('biorealm', 'Antarctic',  'Antarctic', '', '');
$form->addSelect('biorealm', '', 'class=select2, data-width=100%');

$form->setCols(6, 6);

$form->setCols(6, 6);
$form->groupInputs('country', 'region');
$form->addHelper('Country', 'country');
$form->addCountrySelect('country', '', '', array('flag_size' => 16, 'return_value' => 'code', 'placeholder' => 'Select your country'));
$form->setCols(0, 6);
$form->addHelper('Region', 'region');
$form->addInput('text', 'region', '', '', '');
$form->setCols(6, 6);

$form->endFieldset();

#######################
# Clear/Save
#######################
$form->addBtn('submit', 'submit-btn', 1, 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'submit-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this project?\')', 'my-btn-group');
}
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-project', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($project_form->getPageTitle());
$page_render->renderPage();