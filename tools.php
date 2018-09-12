<?php
require_once 'classes/Page_Renderer.php';

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Tools");
$page_render->setInnerHTML('<h1 style="text-align: center">Tools</h1>');
$page_render->noLoginRequired();
$page_render->disableSidebar();
$page_render->renderPage();