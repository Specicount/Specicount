<?php
require_once 'classes/Page_Renderer.php';

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Home");
$page_render->setInnerHTML('
<div style="text-align: center"><h1>Welcome</h1><br><p>Click on Projects to get started!</p></div>');
$page_render->setPageAccess(false);
$page_render->renderPage();