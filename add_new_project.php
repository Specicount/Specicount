<?php

use phpformbuilder\database\Mysql;
use classes\Add_New_Post_Form;

require_once "classes/Page_Renderer.php";
require_once "classes/Add_New_Post_Form.php";

class Project_Form extends Add_New_Post_Form {
    protected function create() {
        parent::create();
        $update_access['project_id'] = $this->update['project_id'];
        $update_access['email'] = Mysql::SQLValue($_SESSION['email']);
        $update_access['access_level'] = Mysql::SQLValue('owner');
        $this->db->insertRow('user_project_access', $update_access);
    }
}



/* ==================================================
    The Form
================================================== */

$form = new Project_Form("project","projects", 'horizontal', 'novalidate', 'bs4');

$form->startFieldset('Project Data');
$form->setCols(6, 6);
$form->groupInputs('project_id', 'biorealm');

$form->addHelper('Project Name', 'project_id');
if ($_GET["edit"]) {
    $form->addInput('text', 'project_id', '', '', 'required, readonly="readonly"');
} else {
    $form->addInput('text', 'project_id', '', '', 'required'); // Need to have warning for code
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
$form->addBtn('submit', 'submit-btn', "save", 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'delete-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this project?\')', 'my-btn-group');
}
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-project', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
if (isset($_GET["edit"])) {
    $page_render->setPageAccess(true, false, false);
} else {
    $page_render->setPageAccess(false, false, false);
}
$page_render->renderPage();