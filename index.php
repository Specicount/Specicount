<?php
require_once 'classes/Page_Renderer.php';
use function functions\printError;
use function functions\printSuccess;

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Home");
$page_render->setInnerHTML('<p style="font-style: italic">Create a project to get started</p>');
$page_render->noLoginRequired();
$page_render->renderPage();