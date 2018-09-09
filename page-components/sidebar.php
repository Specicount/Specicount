<?php
/*
 * This is the sidebar.
 * It currently chages depending on the pages you are on.
 * For instance, if you have selected a sample, it will display things you can do within the sample,
 * otherwise it will display what samples have already been created (there is also an add new button).
 */

use phpformbuilder\database\Mysql;

function printDbErrors($db, $success_msg="Success!", $fail_msg=null, $redirect=false) {
    global $msg; // This variable is printed in the Page_Renderer class
    // If the database has thrown any errors
    if ($db->error()) {
        // If a fail message hasn't been set
        if ($fail_msg == null) {
            // Set the fail message to the given database error
            $fail_msg = $db->error() . '<br>' . $db->getLastSql();
        }
        $msg .= '<p class="alert alert-danger">'.$fail_msg.'</p>';
    } else {
        $msg = '<p class="alert alert-success">'.$success_msg.'</p>';
        if ($redirect) {
            header("Location: index.php");
        }
    }
}

// Sample Side Bar (shown if sample is selected)
if (!empty($_GET["sample_id"])) {
    $sample_id = $_GET["sample_id"];
    $core_id = $_GET["core_id"];
    $project_id = $_GET["project_name"];
    ?>
    <nav class="sidebar bg-dark">
        <ul class="list-unstyled">
            <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="add_new_sample.php?edit=true&project_name=<?= $project_id?>&core_id=<?= $core_id?>&sample_id=<?= $sample_id?>"><i class="fa fa-edit"></i> Edit Sample</a></li>
            <li><a href="sample.php?project_name=<?= $project_id?>&core_id=<?= $core_id?>&sample_id=<?= $sample_id?>"><i class="fa fa-stopwatch"></i> Sample Count</a></li>
            <li><a href="search_specimen.php?project_name=<?= $project_id?>&core_id=<?= $core_id?>&sample_id=<?= $sample_id?>"><i class="fa fa-search"></i> Search Specimen</a></li>
            <li><a href="add_new_specimen.php?project_name=<?= $project_id?>&core_id=<?= $core_id?>&sample_id=<?= $sample_id?>"><i class="fa fa-plus"></i> Add New Specimen</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
    </nav>
    <?php
// Project Side Bar (shown if not in sample - has drop downs of project, core and samples extracted from database)
} else {
    ?>
    <nav class="sidebar bg-dark">
        <ul class="list-unstyled">
            <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="add_new_project.php"><i class="fa fa-plus"></i> Add New Project</a></li>
            <li><a href="add_new_specimen.php"><i class="fa fa-plus"></i> Add New Specimen</a></li>

            <li>
                <?php

                $db = new Mysql();
                $db->selectRows("projects", array("username"=>Mysql::SQLValue($_SESSION['username'])), "project_name", "project_name", true);
                foreach ($db->recordsArray() as $project) {
                    echo "<a href='#".$project["project_name"]."' data-toggle='collapse'><i class='fas fa-folder'></i>  ".$project["project_name"]."</a>
                            <ul id='".$project["project_name"]."' class='list-unstyled collapse'>
                            <li><a href='add_new_project.php?edit=true&project_name=".$project["project_name"]."'><i class='fa fa-edit'></i> Edit Project</a></li>
                            <li><a href='add_new_core.php?project_name=".$project["project_name"]."'><i class='fa fa-plus'></i> Add New Core</a></li>";

                    $db->selectRows("cores", array("project_name" => Mysql::SQLValue($project["project_name"])), "core_id", "core_id", true);
                    foreach ($db->recordsArray() as $core) {
                        echo "<a href='#".$core["core_id"]."' data-toggle='collapse'><i class='fa fa-database'></i> ".$core["core_id"]."</a>
                        <ul id='".$core["core_id"]."' class='list-unstyled collapse'>
                        <li><a href='add_new_core.php?edit=true&project_name=".$project["project_name"]."&core_id=".$core["core_id"]."'><i class='fa fa-edit'></i> Edit Core</a></li>
                        <li><a href='add_new_sample.php?project_name=".$project["project_name"]."&core_id=".$core["core_id"]."' data-parent='#".$core["core_id"]."'><i class='fa fa-plus'></i> Add New Sample</a></li>";

                        $db->selectRows("samples", array("core_id" => Mysql::SQLValue($core["core_id"]), "project_name" => Mysql::SQLValue($project["project_name"])), "sample_id", "sample_id", true);
                        foreach ($db->recordsArray() as $sample) {
                            echo "<li><a href='sample.php?project_name=".$project["project_name"]."&core_id=".$core["core_id"]."&sample_id=".$sample["sample_id"]."'><i class='fa fa-flask'></i> ".$sample["sample_id"]."</a></li>";
                        }
                        echo "</ul>";
                    }

                    echo "</ul>";
                }
                ?>
            </li>
            <li><a href="register.php"><i class="fa fa-user-plus"></i> Add User</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
    </nav>
    <?php
}
?>