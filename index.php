<?php
require_once 'classes/Page_Renderer.php';

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Home");
if (!isset($_SESSION['email'])) {
    $page_render->setInnerHTML('<div style="text-align: center"><h1>Welcome</h1><br><p>Please login to get started!</p></div>');
}
else{
    $page_render->setInnerHTML('<div style="text-align: center"><h1>Welcome</h1><br><p>Please click on projects to get started!</p></div>');
}
$page_render->setPageAccess(false);
$page_render->renderPage();