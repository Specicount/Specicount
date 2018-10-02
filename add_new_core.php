<?php

use classes\Add_New_Post_Form;

require_once "classes/Page_Renderer.php";
require_once "classes/Add_New_Post_Form.php";



/* ==================================================
    The Form
================================================== */

$form = new Add_New_Post_Form("core", "cores", 'horizontal', 'novalidate', 'bs4');

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
$form->addBtn('submit', 'submit-btn', "save", 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'delete-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this core?\')', 'my-btn-group');
}
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-project', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
if (isset($_GET["edit"])) {
    $page_render->setPageAccess(true, true, true, false);
} else {
    $page_render->setPageAccess(true, true, false, false);
}
$page_render->renderPage();