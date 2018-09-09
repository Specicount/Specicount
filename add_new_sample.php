<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

require_once "classes/Page_Renderer.php";
require_once "classes/Abstract_Form.php";


class Sample_Form extends \classes\Abstract_Form {
    public function getFormType() {
        return "sample";
    }

    public function getTableName() {
        return "samples";
    }

    public function delete($db, $filter) {
        $db->deleteRows("concentration_curve", $filter);
        $this->printDbErrors($db);
        $db->deleteRows("found_specimen", $filter);
        $this->printDbErrors($db);
        $db->deleteRows($this->getTableName(), $filter);
    }

    public function submit($db, $update) {
        $update["start_date"] = Mysql::SQLValue($_POST["start_date"], "date");
        $update["last_edit"] = Mysql::SQLValue(date("Y-m-d H:i:s"), "date");
        $db->insertRow($this->getTableName(), $update);
    }

    public function update($db, $update, $filter) {
        $update["start_date"] = Mysql::SQLValue($_POST["start_date"], "date");
        $update["last_edit"] = Mysql::SQLValue(date("Y-m-d H:i:s"), "date");
        $db->updateRows($this->getTableName(), $update, $filter);
    }
}

$sample_form = new Sample_Form();


/* ==================================================
    The Form
================================================== */

$form = new Form($sample_form->getFormName(), 'horizontal', 'novalidate', 'bs4');

$form->addHelper('Sample ID', 'sample_id');

if ($_GET["edit"]) {
    $form->addInput('text', 'sample_id', '', 'Sample ID ', 'required, readonly="readonly"');
} else {
    $form->addInput('text', 'sample_id', '', 'Sample ID ', 'required');
}

# Analyst Name
$form->setCols(4, 4);
$form->groupInputs('analyst_first_name', 'analyst_last_name');
$form->addHelper('First Name', 'analyst_first_name');
$form->addInput('text', 'analyst_first_name', '', 'Analyst ', 'required');
$form->setCols(0, 4);
$form->addHelper('Last Name', 'analyst_last_name');
$form->addInput('text', 'analyst_last_name', '', '', '');
$form->setCols(4, 8);

$form->addPlugin('pickadate', '#start_date');
$form->addInput('text', 'start_date', '', 'Start Date ', 'required');

$form->addHelper('Years Old', 'modelled_age');
$form->addInput('number', 'modelled_age', '', 'Modelled Age');

#######################
# Clear/Save
#######################
$form->addBtn('submit', 'submit-btn', 1, 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'submit-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this sample?\')', 'my-btn-group');
}
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($sample_form->getPageTitle());
$page_render->renderPage();