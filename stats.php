<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 8/10/2018
 * Time: 2:04 PM
 */
require_once "classes/Page_Renderer.php";

$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Analysis");
$page_render->disableSidebar();
$page_render->disableDivContainer();
$page_render->renderPage();

echo "
<script type=\"text/javascript\" src=\"js/iframeResizer.min.js\"></script>
<style>
  iframe {
    height:1000px;
    overflow-x: scroll;
    overflow-y: scroll
  }
</style>
<iframe id=\"myIframe\" src=\"http://seprojgrp2b.anu.edu.au:3838/StatApp/\" scrolling=\"yes\" frameborder=\"no\"></iframe>
<script>
  iFrameResize({
    heightCalculationMethod: 'taggedElement'
  });
</script>
";