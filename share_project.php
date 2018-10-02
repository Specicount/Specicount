<?php
use phpformbuilder\Form;

require_once "classes/Page_Renderer.php";

unset($_SESSION['share']);

$form = new Form('share', 'horizontal', 'novalidate', 'bs4');

$form->setCols(0, 12);

// Render Page
$form->addHelper('Project:', 'project-selection');
$form->addOption('project-selection', 'mp1', 'My Project 1', 'My Projects');
$form->addOption('project-selection', 'mp2', 'My Project 2', 'My Projects');
$form->addOption('project-selection', 'mp3', 'My Project 3', 'My Projects');
$form->addOption('project-selection', 'p1', 'Project 1', 'Shared Projects');
$form->addOption('project-selection', 'p2', 'Project 2', 'Shared Projects');
$form->addSelect('project-selection', '');

$form->addHelper('Share With:', 'share-with');
$form->addInput('text', 'share-with', '', '', 'required');

$form->addHelper('Access Type:', 'access-selection');
$form->addOption('access-selection', 'admin', 'Viewer', '');
$form->addOption('access-selection', 'contributor', 'Contributor', '');
$form->addOption('access-selection', 'viewer', 'Admin', '');
$form->addSelect('access-selection', '');

$form->addBtn('submit', 'submit-btn', 1, 'Share', 'class=btn btn-success ladda-button, data-style=zoom-in');

$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Share A Project");
$page_render->setForm($form);
$page_render->enableSidebar();
$page_render->renderPage();