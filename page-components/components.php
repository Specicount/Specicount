<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 21/09/2018
 * Time: 10:45 AM
 */

use phpformbuilder\database\Mysql;

$current_dir = __DIR__;
require_once $current_dir.'/../phpformbuilder/database/Mysql.php';
require_once $current_dir.'/../phpformbuilder/database/db-connect.php';
require_once $current_dir.'/functions.php';

# Return header
function getHeader() {
    return '
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Bootstrap 4 Form Generator - how to create an Employment Application Form with Php Form Builder Class">
    <link rel="icon" href="../images/pollen.png">
    <style type="text/css">
        fieldset {
            margin-bottom: 80px;
        }
    </style>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/fontawesome.min.css">
    <link rel="stylesheet" href="../css/bsadmin.css">
    <link rel="stylesheet" href="../css/samplegrid.css">
    <link rel="stylesheet" href="../css/styles.css">    
    ';
}

# Return Navbar
function getNavbar ($render_side, $navbar_text) {
    $output = "
    <nav><div class=\"navbar navbar-expand\">
    <div class=\"navbar-nav mr-auto\">";
    if ($render_side) {
        $output .= "<a class=\"sidebar-toggle text-light mr-3\"><i class=\"fa fa-bars\"></i></a>";
    }
    $output .= "<text class=\"text-center\">Specicount - $navbar_text</text>
    </div>
    ";
    // If a user is logged in
    if (isset($_SESSION["email"])) {
        $output .= '<a style="color:white; margin-right: 20px;text-decoration: none;" href="logout.php"><i class="fa fa-sign-out-alt"></i> Log Out</a>';
    } else {
        $output .= "<a style=\"color:white; margin-right: 20px;text-decoration: none;\" href='#' data-remodal-target=\"modal-login-target\"><i class=\"fa fa-sign-in-alt\"></i> Log In</a>";
    }
    $output .= "</div>

    <div class=\"btn-group\" style=\"width: 100%; font-size: 0; min-width: 510px;\">
        <a class=\"ribbon-button\" href=\"index.php\" style=\"width:33.333%\"><i class=\"fa fa-home\"></i> Home</a>
        <div class=\"dropdown\" style=\"width:33.333%\">
            <a href=\"projects.php\"  class=\"ribbon-button\" style=\"width:100%\"><i class=\"fa fa-folder\"></i> Projects</a>
            <div class=\"dropdown-content\" style=\"width:100%\">
                <a href=\"projects.php\">Recent Projects</a>
                <a href=\"add_new_project.php\">New Project</a>
            </div>
        </div>
        <div class=\"dropdown\" style=\"width:33.334%\">
            <a href=\"help.php\"  class=\"ribbon-button\"><i class=\"fa fa-question-circle\"></i> Help</a>
            <div class=\"dropdown-content\">
                <a href=\"stats.php\">Stats</a>
                <a href=\"help.php?tab=FAQ\">Frequently Asked Questions</a>
                <a href=\"help.php?tab=Documentation\">Documentation</a>
                <a href=\"help.php?tab=User_Guide\">How To Use</a>
                <a href=\"mailto:&#097;&#110;&#117;&#046;&#098;&#105;&#111;&#100;&#097;&#116;&#097;&#064;&#103;&#109;&#097;&#105;&#108;&#046;&#099;&#111;&#109;?subject=BioData Bug Report\"><i class=\"fa fa-bug\"></i> Report A Bug</a>
            </div>
        </div>
    </div>
    </nav>
    
    ";
    return $output;
}

# Return scripts
function getScripts () {
    return '<!--This is the necessary scripts for each page-->
        <script src="js/jquery-3.3.1.js"></script>
        <script src="js/jquery.hoverIntent.min.js"></script>
        <script src="js/bootstrap.js"></script>
        <script src="js/popper.js"></script>
        <script src="js/bsadmin.js"></script>
        <script src="js/fontawesome-all.js"></script>
        <!--The following script is for the overlays for each specimen on the counting page (fiddly and a bit ugly but somewhat works be wary if playing with)-->
        <script>
        
        function navigate (link) {
            window.location.href = link;
        }
        
        $(document).ready(function() {
            // This uses the hoverIntent jquery plugin to avoid excessive queuing of animations
            // If mouse intends to hover over specimen
            function enter() {
                var specimen_id = $(this).attr(\'id\').split("_")[0];
                fadeInOverlay(specimen_id);
            }
            function leave() {
                var specimen_id = $(this).attr(\'id\').split("_")[0];
                fadeOutOverlay(specimen_id);
            }
            $(".specimen-container").hoverIntent(enter, leave);
        
            //If close button on overlay clicked
            $(".overlay .top-right-btn").click(function() {
                var specimen_id = $(this).attr(\'id\').split("_")[0];
                fadeOutOverlay(specimen_id);
            });
        
            function fadeInOverlay(specimen_id) {
                $("#"+specimen_id+"_overlay").fadeIn(200);
                $("#"+specimen_id+"_counter").fadeOut(200);
            }
        
            function fadeOutOverlay(specimen_id) {
                $("#"+specimen_id+"_overlay").fadeOut(200);
                $("#"+specimen_id+"_counter").fadeIn(200);
            }
        
            $("p.alert").click(function() {
                $(this).fadeOut(200);
            });
            
            $(".rowlink").click(function() {
                window.location = $(this).data("link");
            });
        });
        </script>';
}

# Return Sidebar
function getSidebar () {
    // Sample Side Bar (shown if sample is selected)

    if(!isset($_SESSION["email"])) return "";

    $output = '';
    if (!empty($_GET["sample_id"])) {
        $sample_id = $_GET["sample_id"];
        $core_id = $_GET["core_id"];
        $project_id = $_GET["project_id"];
        $my_access_level = getAccessLevel();

        $output .= '<nav class="sidebar bg-dark">
            <ul class="list-unstyled">';
        if (basename($_SERVER['PHP_SELF']) == "sample.php") {
            $output .= '<li><a href="projects.php?project_id='.$project_id.'&core_id='.$core_id.'"><i class="fa fa-reply"></i> Return to Core</a></li>';
            if ($my_access_level == "visitor") {
                $output .= '<li><a href="add_new_sample.php?edit=true&project_id='.$project_id.'&core_id='.$core_id.'&sample_id='.$sample_id.'"><i class="fa fa-info-circle"></i> View Sample Details</a></li>';
            } else {
                $output .= '<li><a href="add_new_sample.php?edit=true&project_id='.$project_id.'&core_id='.$core_id.'&sample_id='.$sample_id.'"><i class="fa fa-edit"></i> Edit Sample</a></li>';
            }
            $output .= '<li><a href="search_specimen.php?project_id='.$project_id.'&core_id='.$core_id.'&sample_id='.$sample_id.'"><i class="fa fa-search"></i> Search Specimen</a></li>';
            if ($my_access_level != "visitor") {
                $output .= '<li><a href="add_new_specimen.php?project_id='.$project_id.'&core_id='.$core_id.'&sample_id='.$sample_id.'"><i class="fa fa-plus"></i> Add New Specimen</a></li>';
            }
        } else {
            $output .= '<li><a href="sample.php?project_id='.$project_id.'&core_id='.$core_id.'&sample_id='.$sample_id.'"><i class="fas fa-reply"></i> Return to Sample</a></li>';
        }
        $output .= '</ul></nav>';


    // Project Side Bar (shown if not in sample - has drop downs of project, core and samples extracted from database)
    } else {
        $output .= '<nav class="sidebar bg-dark">';
            $db = new Mysql();
            $project_id = $_GET['project_id'];
            $my_access_level = getAccessLevel();
            // If currently on a page that is connected to this project, expand the project dropdown
            // Print the projects
            $output .= "<ul id='".$project_id."' class='list-unstyled'>
                        <li><a href='project_access.php?project_id=".$project_id."'><i class='fa fa-users'></i> View Project Users</a></li>";
            if ($my_access_level == "visitor") {
                $output .= "<li><a href='add_new_project.php?edit=true&project_id=".$project_id."'><i class='fa fa-info-circle'></i> View Project Details</a></li>";
            } else {
                $output .= "<li><a href='add_new_project.php?edit=true&project_id=".$project_id."'><i class='fa fa-edit'></i> Edit Project</a></li>";
            }

            if ($my_access_level != "visitor") {
                $output .= "<li><a href='add_new_specimen.php?project_id=".$project_id."'><i class='fa fa-plus'></i> Add New Specimen</a></li>
                            <li><a href='add_new_core.php?project_id=".$project_id."'><i class='fa fa-plus'></i> Add New Core</a></li>";
            }
            $db->selectRows("cores", array("project_id" => Mysql::SQLValue($project_id)), "core_id", "core_id", true);
            foreach ($db->recordsArray() as $core_array) {
                $core_id = $core_array["core_id"];
                // If currently on a page that is connected to this core, expand the core dropdown
                $toggle_expand_parent = $toggle_expand_child = "";
                if ($_GET["core_id"] == $core_id) {
                    $toggle_expand_parent = 'aria-expanded="true"';
                    $toggle_expand_child = 'show';
                }
                // Print the cores
                $output .= "<a href='#".$core_id."' data-toggle='collapse' ".$toggle_expand_parent."><i class='fa fa-database'></i> ".$core_id."</a>
                        <ul id='".$core_id."' class='list-unstyled collapse ".$toggle_expand_child."'>";
                if ($my_access_level == "visitor") {
                    $output .= "<li><a href='add_new_core.php?edit=true&project_id=".$project_id."&core_id=".$core_id."'><i class='fa fa-info-circle'></i> View Core Details</a></li>";
                } else {
                    $output .= "<li><a href='add_new_core.php?edit=true&project_id=".$project_id."&core_id=".$core_id."'><i class='fa fa-edit'></i> Edit Core</a></li>";
                }

                if ($my_access_level != "visitor") {
                    $output .= "<li><a href='add_new_sample.php?project_id=".$project_id."&core_id=".$core_id."' data-parent='#".$core_id."'><i class='fa fa-plus'></i> Add New Sample</a></li>";
                }

                $db->selectRows("samples", array("core_id" => Mysql::SQLValue($core_id), "project_id" => Mysql::SQLValue($project_id)), "sample_id", "sample_id", true);
                foreach ($db->recordsArray() as $sample_array) {
                    $sample_id = $sample_array["sample_id"];

                    // Print the samples
                    $output .= "<li><a href='sample.php?project_id=".$project_id."&core_id=".$core_id."&sample_id=".$sample_id."'><i class='fa fa-flask'></i> ".$sample_id."</a></li>";
                }
                $output .= "</ul>";
            }

            $output .= "</ul>";
            $output .= '</nav>';
    }
    return $output;
}