<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

require_once "classes/Page_Renderer.php";
require_once "classes/Abstract_Form.php";


class Core_Form extends \classes\Abstract_Form {
    public function getFormType() {
        return "core";
    }
}


$core_form = new Core_Form();

/* ==================================================
    The Form
================================================== */

$form = new Form($core_form->getFormName(), 'horizontal', 'novalidate', 'bs4');

$form->addHelper('Core ID', 'core_id');

if ($_GET["edit"]) {
    $form->addInput('text', 'core_id', '', 'Core ID ', 'required, readonly="readonly"');
} else {
    $form->addInput('text', 'core_id', '', 'Core ID');
}


$form->addPlugin('tinymce', '#description', 'contact-config');
$form->addTextarea('description', '', 'Core Notes');

#######################
# Clear/Save
#######################
$form->addBtn('submit', 'submit-btn', 1, 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'submit-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this core?\')', 'my-btn-group');
}
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-project', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($core_form->getPageTitle());
$page_render->disableSidebar();
$page_render->renderPage();