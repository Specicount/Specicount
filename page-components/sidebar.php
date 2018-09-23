<?php
/*
 * This is the sidebar.
 * It currently chages depending on the pages you are on.
 * For instance, if you have selected a sample, it will display things you can do within the sample,
 * otherwise it will display what samples have already been created (there is also an add new button).
 */

use phpformbuilder\database\Mysql;

// Sample Side Bar (shown if sample is selected)
if (!empty($_GET["sample_id"])) {
    $sample_id = $_GET["sample_id"];
    $core_id = $_GET["core_id"];
    $project_id = $_GET["project_id"];
    ?>
    <nav class="sidebar bg-dark">
        <ul class="list-unstyled">
            <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="add_new_sample.php?edit=true&project_id=<?= $project_id?>&core_id=<?= $core_id?>&sample_id=<?= $sample_id?>"><i class="fa fa-edit"></i> Edit Sample</a></li>
            <li><a href="sample.php?project_id=<?= $project_id?>&core_id=<?= $core_id?>&sample_id=<?= $sample_id?>"><i class="fa fa-stopwatch"></i> Sample Count</a></li>
            <li><a href="search_specimen.php?project_id=<?= $project_id?>&core_id=<?= $core_id?>&sample_id=<?= $sample_id?>"><i class="fa fa-search"></i> Search Specimen</a></li>
            <li><a href="add_new_specimen.php?project_id=<?= $project_id?>&core_id=<?= $core_id?>&sample_id=<?= $sample_id?>"><i class="fa fa-plus"></i> Add New Specimen</a></li>
        </ul>
    </nav>
    <?php
// Project Side Bar (shown if not in sample - has drop downs of project, core and samples extracted from database)
} else {
    ?>
    <nav class="sidebar bg-dark">
        <ul class="list-unstyled">
            <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
            <?php
                if (isset($_SESSION["email"])) {
                    echo '<li><a href="add_new_project.php"><i class="fa fa-plus"></i> Add New Project</a></li>';
                }
            ?>
            <li>
                <?php

                $db = new Mysql();
                $email = Mysql::SQLValue($_SESSION['email']);
                $sql = "SELECT project_id FROM user_project_access NATURAL JOIN projects WHERE email =".$email." ORDER BY project_id";
                $db->query($sql);
                foreach ($db->recordsArray() as $project) {
                    $toggle_expand_parent = $toggle_expand_child = "";
                    if ($_GET["project_id"] == $project["project_id"]) {
                        $toggle_expand_parent = 'aria-expanded="true"';
                        $toggle_expand_child = 'show';
                    }
                    echo "<a href='#".$project["project_id"]."' data-toggle='collapse' ".$toggle_expand_parent."><i class='fas fa-folder'></i>  ".$project["project_id"]."</a>
                            <ul id='".$project["project_id"]."' class='list-unstyled collapse ".$toggle_expand_child."'>
                            <li><a href='leave_project.php?project_id=".$project["project_id"]."'><i class='fa fa-sign-out-alt'></i> Leave Project</a></li>
                            <li><a href='add_new_project.php?edit=true&project_id=".$project["project_id"]."'><i class='fa fa-edit'></i> Edit Project</a></li>
                            <li><a href='project_access.php?project_id=".$project["project_id"]."'><i class='fa fa-users'></i> View Project Users</a></li>
                            <li><a href='add_new_specimen.php?project_id=".$project["project_id"]."'><i class='fa fa-plus'></i> Add New Specimen</a></li>
                            <li><a href='add_new_core.php?project_id=".$project["project_id"]."'><i class='fa fa-plus'></i> Add New Core</a></li>";

                    $db->selectRows("cores", array("project_id" => Mysql::SQLValue($project["project_id"])), "core_id", "core_id", true);
                    foreach ($db->recordsArray() as $core) {
                        $toggle_expand_parent = $toggle_expand_child = "";
                        if ($_GET["core_id"] == $core["core_id"]) {
                            $toggle_expand_parent = 'aria-expanded="true"';
                            $toggle_expand_child = 'show';
                        }
                        echo "<a href='#".$core["core_id"]."' data-toggle='collapse' ".$toggle_expand_parent."><i class='fa fa-database'></i> ".$core["core_id"]."</a>
                        <ul id='".$core["core_id"]."' class='list-unstyled collapse ".$toggle_expand_child."'>
                        <li><a href='add_new_core.php?edit=true&project_id=".$project["project_id"]."&core_id=".$core["core_id"]."'><i class='fa fa-edit'></i> Edit Core</a></li>
                        <li><a href='add_new_sample.php?project_id=".$project["project_id"]."&core_id=".$core["core_id"]."' data-parent='#".$core["core_id"]."'><i class='fa fa-plus'></i> Add New Sample</a></li>";

                        $db->selectRows("samples", array("core_id" => Mysql::SQLValue($core["core_id"]), "project_id" => Mysql::SQLValue($project["project_id"])), "sample_id", "sample_id", true);
                        foreach ($db->recordsArray() as $sample) {
                            echo "<li><a href='sample.php?project_id=".$project["project_id"]."&core_id=".$core["core_id"]."&sample_id=".$sample["sample_id"]."'><i class='fa fa-flask'></i> ".$sample["sample_id"]."</a></li>";
                        }
                        echo "</ul>";
                    }

                    echo "</ul>";
                }
                ?>
            </li>
        </ul>
    </nav>
    <?php
}
?>