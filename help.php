
<?php
require_once 'classes/Page_Renderer.php';
/*
Collapsible sections modified from www.w3schools.com
*/

$html_string = '
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
  <p style="padding-top: 10px; font-weight: bold;">Help! I can’t access the projects page</p>
  <p>Don’t panic, this is likely because you’re not logged in or the session has expired.<br>
     Please click on the accounts tab to check if you’re currently logged in.<br>
     If you still don’t access, please go to the Help tab and click report a bug to report your issue.
  </p>
  <p style="padding-top: 10px; font-weight: bold;">My projects page is empty!</p>
  <p>No need to worry, this is likely because you don’t have any projects. Just click new project in the projects tab to create a fresh project. Once this is done you should be able to see and click on your new project. Alternatively, if you are collaborating with another member, ask them to share their project with you so that you can start collaborating!
     <br><br>
     If you did have projects previously and your project page is empty, please check that your current account is correct, otherwise this is an issue and should be reported immediately.
  </p>
  <p style="padding-top: 10px; font-weight: bold;">I\'ve forgotten my password!</p>
  <p>No need to worry! You can reset your password by getting a password reset link.
     <br>Simply go to the normal login page and click \'Forgot Password\'.
     <br>Enter your email address and a password reset link will be sent to you with instructions on how to reset your password.
     <br><br>If you still have any problems, or you forgot your email please contact support about your issue.
  </p>
  <p style="padding-top: 10px; font-weight: bold;">Note:  As this is a beta release, if you have any issues - please do not hesitate to use the report a bug feature. We will try get back to you as soon as possible.</p>
</div>
<br>
<button class="collapsible" id="Documentation">Documentation</button>
<div class="content">
  <p style="padding-top: 10px; font-weight: bold;">For documentation, please see the User Manual for full list of features and functionalities</p>
</div>
<br>
<button class="collapsible" id="User_Guide">User Guide</button>
<div class="content">
  <p style="padding-top: 10px; font-weight: bold;">"Note: This is a brief user guide to get started. For the full user guide please see the user manual.</p>
  <p style="padding-top: 10px; font-weight: bold;">Register and Login</p>
  <p>First, please register and login to gain access to the projects page as well as the Specicount software.
     This can be done through he accounts tab, please use a valid email address, strong password and try to fill the fields as accurately as possible. 
     <br><br>By universal law your data will not be used for anything besides authentication and contact purposes, your privacy is our concern and we will ensure that your data is safely stored. 
  </p>
   <p style="padding-top: 10px; font-weight: bold;">Projects page and getting started</p>
   <p>To get started you first want to create a project, unless you’ve been invited to collaborate with another member. 
     <br>To create a project, click on the projects tab and click on the ‘New Project’ tab. Fill in the required fields and click ‘Save’.
     <br><br>Now please see the next steps for Specimens, Cores and Samples.
  </p>
  <p style="padding-top: 10px; font-weight: bold;"> Specimens, Cores and Samples</p>
  <p>To create a specimen, core or sample simply go open the project you have created or been invited too and a sidebar should appear on the left.
     <br><br>Click ‘add new specimen’ on the sidebar to create a new specimen and fill in the required fields. Your created specimen will now be visible to samples, which means you must create a core and sample to view it.
     <br><br>Click ‘add new core’ on the sidebar to create a new core and fill in the required fields. Once a core has been created you should notice it appear on the sidebar with the name of the Core ID you used.
     <br><br>Samples are linked to cores; therefore, a core must be created first. Once the core has been created click on the core and you should see a ‘add new sample’. Click on this and fill in the required fields and it should appear bellow your core now. Now click on the sample and you should now be able to add new specimens to the sample or search for one you have made previously.
  </p>
  <p style="padding-top: 10px; font-weight: bold;">Deleting</p>
  <p>To delete a project, specimen, core or sample, simply click the edit tab for the relevant object and click the delete button.</p>
  <p style="padding-top: 10px; font-weight: bold;">Sharing your project</p>
  <p>To share your project, click on your project and click view projected users on the sidebar. Then enter the email of the person you want to add and select the access level which you want to give them.</p>
  <p style="padding-top: 10px; font-weight: bold;">Reporting a bug/Feedback on Specicount</p>
  <p>If you find any ‘bugs’/issues, concerns or feedback please click on the report a bug tab on the help main help tab. You will be redirected to your email client and you can send an email with your problem there. 
     <br><br>Please keep the subject related to your issue and describe as best you can how you got to you issue -so we can fast track and recreate your problem. 
     <br><br>We will get back to you as soon as possible and hopefully your problem will be fixed by our development team.
   </p>
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
';

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Help");
$page_render->setInnerHTML($html_string);
$page_render->disableSidebar();
$page_render->renderPage();
