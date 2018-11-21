<?php

use phpformbuilder\database\Mysql;
use classes\Add_New_Post_Form;

require_once "classes/Page_Renderer.php";
require_once "classes/Add_New_Post_Form.php";

class Sample_Form extends Add_New_Post_Form {
    protected function setUpdateArray() {
        parent::setUpdateArray();
        $this->update["start_date"] = Mysql::SQLValue($_POST["start_date"], "date");
        $this->update["last_edit"] = Mysql::SQLValue(date("Y-m-d H:i:s"), "date");
        $this->update["depth"] = Mysql::SQLValue($_POST["depth"], "float");
        $this->update["age"] = Mysql::SQLValue($_POST["age"], "int");
        $this->update["volume"] = Mysql::SQLValue($_POST["volume"], "float");
        $this->update["total_spike"] = Mysql::SQLValue($_POST["total_spike"], "int");
    }
}

/* ==================================================
    The Form
================================================== */

$form = new Sample_Form("sample", "samples",'horizontal', 'novalidate', 'bs4');
$my_access_level = getAccessLevel();
$readonly_attr = "";
if ($my_access_level == "visitor") {
    $readonly_attr = "readonly=readonly, ";
}



if ($_GET["edit"]) {
    $form->addInput('text', 'sample_id', '', 'Sample ID', 'required, readonly=readonly');
} else {
    $form->addInput('text', 'sample_id', '', 'Sample ID', 'required');
}

# Fill in first and last name with logged in user's first and last name
$db = new Mysql();
$filter["email"] = Mysql::sqlValue($_SESSION["email"]);
$db->selectRows("users", $filter);
$user = $db->recordsArray()[0];
$_SESSION[$form->getFormName()]['analyst_first_name'] = $user['first_name'];
$_SESSION[$form->getFormName()]['analyst_last_name'] = $user['last_name'];

# Analyst Name
$form->setCols(4, 4);
$form->groupInputs('analyst_first_name', 'analyst_last_name');
$form->addHelper('First Name', 'analyst_first_name');
$form->addInput('text', 'analyst_first_name', '', 'Analyst', $readonly_attr.'required');
$form->setCols(0, 4);
$form->addHelper('Last Name', 'analyst_last_name');
$form->addInput('text', 'analyst_last_name', '', '', $readonly_attr.'required');
$form->setCols(4, 8);

if ($my_access_level != "visitor") {
    $form->addPlugin('pickadate', '#start_date');
}
$form->addInput('text', 'start_date', '', 'Start Date', $readonly_attr.'required');

$form->addHelper('Depth (cm)', 'depth');
$form->addInput('number', 'depth', '', 'Depth', $readonly_attr);

$form->addHelper('Calibrated Years (Before Present)', 'age');
$form->addInput('number', 'age', '', 'Modelled Age', $readonly_attr);

$form->addHelper('Volume (cubic cm)', 'volume');
$form->addInput('number', 'volume', '', 'Volume', $readonly_attr);

$form->addHelper('The total number of exotic spikes per tablet added to each sample', 'total_spike');
$form->addInput('number', 'total_spike', '', 'Total Spike', $readonly_attr);

#######################
# Clear/Save
#######################
if ($my_access_level != "visitor") {
    $form->addBtn('submit', 'submit-btn', "save", 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
    $form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
    if ($_GET["edit"]) {
        $form->addBtn('submit', 'delete-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this sample?\')', 'my-btn-group');
    }
    $form->printBtnGroup('my-btn-group');
}

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
if (isset($_GET["edit"])) {
    $page_render->setPageRestrictions(true, true, true, true);
} else {
    $page_render->setPageRestrictions(true, true, true, false);
}
$page_render->renderPage();