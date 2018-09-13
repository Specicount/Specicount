<?php
require_once 'classes/Page_Renderer.php';
use function functions\printError;
use function functions\printSuccess;

if ($_GET["error"]) {
    printError($_GET["error"]);
    switch ($_GET["error"]) {
        case "no_project_access" : printError(); break;
        case "invalid_permissions" : printError("You do not have the correct permissions to perform those changes"); break;
    }
}

if ($_GET["success"]) {
    printSuccess($_GET["success"]);
}

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Home");
$page_render->setInnerHTML('<p style="font-style: italic">Create a project to get started</p>');
$page_render->noLoginRequired();
$page_render->renderPage();