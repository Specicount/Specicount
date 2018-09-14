<?php
require_once 'classes/Page_Renderer.php';
use function functions\printError;
use function functions\printSuccess;

/*if ($_GET["error_message"]) {
    printError($_GET["error_message"]);
}

if ($_GET["success_message"]) {
    printSuccess($_GET["success_message"]);
}*/

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Home");
$page_render->setInnerHTML('<p style="font-style: italic">Create a project to get started</p>');
$page_render->noLoginRequired();
$page_render->renderPage();