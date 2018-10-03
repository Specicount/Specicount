
<?php
require_once 'classes/Page_Renderer.php';
/*
Collapsible sections modified from www.w3schools.com
*/
// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Help");
$page_render->setInnerHTML('
<style>
.collapsible {
  background-color: #343a40;
  color: white;
  cursor: pointer;
  padding: 18px;
  width: 100%;
  border: none;
  text-align: center;
  outline: none;
  font-size: 15px;
  border-radius: 5px;

}
button:focus {outline:0;}

.active, .collapsible:hover {
  background-color: #555;
}

.collapsible:after {
  content: \'\002B\';
  color: white;
  font-weight: bold;
  float: right;
  margin-left: 5px;
}

.active:after {
  content: "\2212";
}

.content {
  padding: 0 18px;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.2s ease-out;
  background-color: #f1f1f1;
}
</style>

<h1 style="text-align: center">Help</h1>
<br>
<button class="collapsible" id="FAQ">FAQ</button>
<div class="content">
  <p style="padding-top: 10px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer aliquet sapien id sapien interdum, ac euismod sapien mollis. Aenean lacinia libero id diam viverra, eu vestibulum lacus convallis. Fusce lacus enim, malesuada in lorem et, venenatis bibendum lacus. Ut vestibulum erat nibh, eget ullamcorper risus pretium in. Ut fermentum convallis congue. Nulla semper varius imperdiet. Curabitur convallis, nibh sed eleifend luctus, erat ex tincidunt enim, vel ornare felis lectus non quam. Donec rutrum elit vel rhoncus gravida. Vivamus ornare velit vitae quam venenatis accumsan. Sed ac massa posuere, ultrices dui nec, tincidunt elit. Proin ut cursus lorem, ac molestie dui.</p>
  <p style="padding-top: 10px;">Sed porta lorem rutrum nisl fringilla ullamcorper. Donec tristique varius lectus, nec ultrices nisl lacinia a. Phasellus sollicitudin tortor nec tincidunt pharetra. Maecenas placerat euismod urna non malesuada. Nunc ac enim libero. Integer vehicula diam sed sagittis elementum. Aenean sit amet vestibulum augue. Nunc facilisis pulvinar eros, sit amet blandit nunc congue vitae. Cras laoreet enim a volutpat pellentesque. Duis aliquam ex quis est bibendum, et pulvinar ligula aliquam. Nunc non lorem ligula. Nam rhoncus diam eget tempor finibus. Praesent diam dui, venenatis nec ligula vel, ultricies mollis dolor.</p>
</div>
<br>
<button class="collapsible" id="Documentation">Documentation</button>
<div class="content">
  <p style="padding-top: 10px;">Integer ultricies elementum tortor sed iaculis. Fusce ut mauris bibendum, elementum quam id, blandit velit. Etiam eget vehicula est, et consectetur justo. Duis lorem nunc, cursus eget pretium gravida, convallis sed ex. Cras in viverra ante. Integer imperdiet velit ac urna fermentum, vel ultricies diam congue. Mauris iaculis erat eros, vel ornare eros pretium a. Phasellus at dapibus ipsum. Donec rutrum lorem nec sapien molestie, sit amet tempus nunc mollis. In at eleifend urna, quis consequat eros. Fusce ut lorem sagittis, molestie nunc a, convallis erat. Praesent eget odio cursus, aliquam massa vel, hendrerit velit. Pellentesque ornare fermentum lobortis. Sed congue enim vitae sodales feugiat. Fusce urna quam, laoreet sit amet lorem vel, euismod condimentum risus. Nullam commodo dolor in tristique ullamcorper.</p>
</div>
<br>
<button class="collapsible" id="User_Guide">User Guide</button>
<div class="content">
  <p style="padding-top: 10px;">Vestibulum pulvinar ut nibh eu volutpat. Aliquam vel tempor libero. Donec at interdum metus. Donec libero justo, mollis sit amet mi non, mattis vehicula justo. Suspendisse congue sagittis eros, vitae dignissim nisl. Integer venenatis tincidunt orci, at venenatis orci. Phasellus eu finibus mi. Donec est odio, lacinia ut lorem at, gravida tristique dui. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas nec lorem mattis erat consectetur commodo. Maecenas justo dui, commodo sed auctor nec, mollis dapibus neque. Quisque quis tortor tellus. Suspendisse bibendum metus id posuere porttitor. Nunc sit amet velit congue, suscipit purus id, rhoncus arcu. Curabitur dapibus dolor sit amet eros auctor, at luctus est euismod.</p>
  <p style="padding-top: 10px;">Aenean aliquet libero a bibendum consequat. Quisque sem turpis, eleifend id sodales at, venenatis eget massa. Mauris vitae interdum nibh. Proin ultricies ornare lacus, et consequat ante iaculis vitae. Quisque et posuere metus, vel scelerisque dolor. Aliquam auctor enim imperdiet, volutpat ipsum vel, congue lacus. Quisque blandit turpis quis est feugiat, a bibendum nunc varius. Sed suscipit porta congue. Nam tempus, elit quis auctor sodales, massa mi eleifend lorem, vitae cursus est purus vitae justo. Praesent sit amet eros non enim vulputate porttitor. Proin leo justo, bibendum vel tempus a, ultrices quis risus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In elementum leo odio, eget imperdiet nulla dignissim id. Pellentesque a dapibus libero, nec pulvinar sem. Morbi eget accumsan nibh, at consequat nulla.</p>
</div>
<br>
<script>
var coll = document.getElementsByClassName("collapsible");
var quick_open = document.URL.split("=")[1];

for (var i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.maxHeight){
      content.style.maxHeight = null;
    } else {
      content.style.maxHeight = content.scrollHeight + "px";
    } 
  });
  if(quick_open && quick_open == coll[i].id){
    coll[i].classList.toggle("active");
    coll[i].nextElementSibling.style.maxHeight = coll[i].nextElementSibling.scrollHeight + "px";
    quick_open = null;
  }
}
</script>
');
$page_render->setPageAccess(false);
$page_render->renderPage();
$page_render->disableSidebar();