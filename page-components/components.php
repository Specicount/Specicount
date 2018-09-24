<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 21/09/2018
 * Time: 10:45 AM
 */

$current_dir = __DIR__;
require_once $current_dir.'/../phpformbuilder/database/Mysql.php';
require_once $current_dir.'/../phpformbuilder/database/db-connect.php';
require_once $current_dir.'/functions.php';

use phpformbuilder\database\Mysql;

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
    $output = "<nav class=\"navbar navbar-expand\">
    <div class=\"navbar-nav mr-auto\">";
    if ($render_side) {
        $output .= "<a class=\"sidebar-toggle text-light mr-3\"><i class=\"fa fa-bars\"></i></a>";
    }
    $output .= "<text class=\"text-center\">$navbar_text</text>
    </div>";
    // If a user is logged in
    if (isset($_SESSION["email"])) {
        $output .= '<a style="color:white; margin-right: 20px;text-decoration: none;" href="logout.php"><i class="fa fa-sign-out-alt"></i> Log Out</a>';
    } else {
        $output .= "<a style=\"color:white; margin-right: 20px;text-decoration: none;\" href='#' data-remodal-target=\"modal-login-target\"><i class=\"fa fa-sign-in-alt\"></i> Log In</a>";
    }
    $output .= "</nav>";
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
        $(document).ready(function() {
            // This uses the hoverIntent jquery plugin to avoid excessive queuing of animations
            // If mouse intends to hover over specimen
            $(".specimen-container").hoverIntent(
                function() {
                    var specimen_id = $(this).attr(\'id\').split("_")[0];
                    fadeInOverlay(specimen_id);
                },
                function() {
                    var specimen_id = $(this).attr(\'id\').split("_")[0];
                    fadeOutOverlay(specimen_id);
                });
        
            //If close button on overlay clicked
            $(".overlay .close-btn").click(function() {
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
        });
        </script>';
}

# Return Sidebar
function getSidebar () {
    // Sample Side Bar (shown if sample is selected)

    $output = '';
    
    if (!empty($_GET["sample_id"])) {
        $sample_id = $_GET["sample_id"];
        $core_id = $_GET["core_id"];
        $project_id = $_GET["project_id"];
        $my_access_level = getAccessLevel();
        $output .= '<nav class="sidebar bg-dark">
            <ul class="list-unstyled">
                <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="add_new_sample.php?edit=true&project_id='.$project_id.'&core_id='.$core_id.'&sample_id='.$sample_id.'"><i class="fa fa-edit"></i> Edit Sample</a></li>
                <li><a href="sample.php?project_id='.$project_id.'&core_id='.$core_id.'&sample_id='.$sample_id.'"><i class="fas fa-stopwatch"></i> Sample Count</a></li>
                <li><a href="search_specimen.php?project_id='.$project_id.'&core_id='.$core_id.'&sample_id='.$sample_id.'"><i class="fa fa-search"></i> Search Specimen</a></li>
                <li><a href="add_new_specimen.php?project_id='.$project_id.'&core_id='.$core_id.'&sample_id='.$sample_id.'"><i class="fa fa-plus"></i> Add New Specimen</a></li>
            </ul>
        </nav>';
    // Project Side Bar (shown if not in sample - has drop downs of project, core and samples extracted from database)
    } else {
        $output .= '<nav class="sidebar bg-dark">
            <ul class="list-unstyled">
            <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>';
        if (isset($_SESSION["email"])) {
            $output .= '<li><a href="add_new_project.php"><i class="fa fa-plus"></i> Add New Project</a></li>';
        }
        $output .= '<li>';

            $db = new Mysql();
            $email = Mysql::SQLValue($_SESSION['email']);
            $sql = "SELECT project_id FROM user_project_access NATURAL JOIN projects WHERE email =".$email." ORDER BY project_id";
            $db->query($sql);
            foreach ($db->recordsArray() as $project) {
                $my_access_level = getAccessLevel($email, $project["project_id"]);
                // If currently on a page that is connected to this project, expand the project dropdown
                $toggle_expand_parent = $toggle_expand_child = "";
                if ($_GET["project_id"] == $project["project_id"]) {
                    $toggle_expand_parent = 'aria-expanded="true"';
                    $toggle_expand_child = 'show';
                }
                // Print the projects
                $output .= "<a href='#".$project["project_id"]."' data-toggle='collapse' ".$toggle_expand_parent."><i class='fas fa-folder'></i>  ".$project["project_id"]."</a>
                        <ul id='".$project["project_id"]."' class='list-unstyled collapse ".$toggle_expand_child."'>
                        <li><a href='leave_project.php?project_id=".$project["project_id"]."'><i class='fa fa-sign-out-alt'></i> Leave Project</a></li>
                        <li><a href='project_access.php?project_id=".$project["project_id"]."'><i class='fa fa-users'></i> View Project Users</a></li>
                        <li><a href='add_new_project.php?edit=true&project_id=".$project["project_id"]."'><i class='fa fa-edit'></i> Edit Project</a></li>";
                if ($my_access_level != "visitor") {
                    $output .= "<li><a href='add_new_specimen.php?project_id=".$project["project_id"]."'><i class='fa fa-plus'></i> Add New Specimen</a></li>
                            <li><a href='add_new_core.php?project_id=".$project["project_id"]."'><i class='fa fa-plus'></i> Add New Core</a></li>";
                }
                $db->selectRows("cores", array("project_id" => Mysql::SQLValue($project["project_id"])), "core_id", "core_id", true);
                foreach ($db->recordsArray() as $core) {
                    // If currently on a page that is connected to this core, expand the core dropdown
                    $toggle_expand_parent = $toggle_expand_child = "";
                    if ($_GET["core_id"] == $core["core_id"]) {
                        $toggle_expand_parent = 'aria-expanded="true"';
                        $toggle_expand_child = 'show';
                    }
                    // Print the cores
                    $output .= "<a href='#".$core["core_id"]."' data-toggle='collapse' ".$toggle_expand_parent."><i class='fa fa-database'></i> ".$core["core_id"]."</a>
                            <ul id='".$core["core_id"]."' class='list-unstyled collapse ".$toggle_expand_child."'>
                            <li><a href='add_new_core.php?edit=true&project_id=".$project["project_id"]."&core_id=".$core["core_id"]."'><i class='fa fa-edit'></i> Edit Core</a></li>";
                    if ($my_access_level != "visitor") {
                        $output .= "<li><a href='add_new_sample.php?project_id=".$project["project_id"]."&core_id=".$core["core_id"]."' data-parent='#".$core["core_id"]."'><i class='fa fa-plus'></i> Add New Sample</a></li>";
                    }

                    $db->selectRows("samples", array("core_id" => Mysql::SQLValue($core["core_id"]), "project_id" => Mysql::SQLValue($project["project_id"])), "sample_id", "sample_id", true);
                    foreach ($db->recordsArray() as $sample) {
                        // Print the samples
                        $output .= "<li><a href='sample.php?project_id=".$project["project_id"]."&core_id=".$core["core_id"]."&sample_id=".$sample["sample_id"]."'><i class='fa fa-flask'></i> ".$sample["sample_id"]."</a></li>";
                    }
                    $output .= "</ul>";
                }

                $output .= "</ul>";
            }
            $output .= '</li>
                </ul>
            </nav>';
    }
    return $output;
}