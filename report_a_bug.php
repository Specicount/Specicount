<?php
use phpformbuilder\Form;

require_once "classes/Page_Renderer.php";

unset($_SESSION['bugreport']);

$form = new Form('bugreport', 'horizontal', 'novalidate', 'bs4');

$form->setCols(0, 11);

$form->addHtml('<h1 style="text-align: center">Bug Report</h1>');
$form->addTextarea('bug-description', '', 'Behaviour',
    'placeholder=Explain the behaiviour of the bug here');
$form->addTextarea('bug-replication', '', 'Replication',
    'placeholder=Describe the steps to recreate your bug');

$form->addBtn('submit', 'submit-btn', 1, 'Submit bug report <i class="fa fa-share" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
$form->printBtnGroup('my-btn-group');

$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle("Report A Bug");
#$page_render->setPageAccess(false); #Logged in for bugs reports?
$page_render->renderPage();