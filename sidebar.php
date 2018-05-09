<?php
use phpformbuilder\database\Mysql;
?>
<?php
// Sample Side Bar
if (!empty($_GET["sample"])) {
    $sample = $_GET["sample"];
    $core = $_GET["core"];
    $project = $_GET["project"];
    ?>
    <nav class="sidebar bg-dark">
        <ul class="list-unstyled">
            <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="add_new_sample.php?project=<?= $project?>&core=<?= $core?>&sample=<?= $sample?>&edit=<?= $sample?>"><i class="fa fa-edit"></i> Edit Sample</a></li>
            <li><a href="sample.php?project=<?= $project?>&core=<?= $core?>&sample=<?= $sample?>"><i class="fa fa-stopwatch"></i> Sample Count</a></li>
            <li><a href="search_specimen.php?project=<?= $project?>&core=<?= $core?>&sample=<?= $sample?>"><i class="fa fa-search"></i> Search Specimen</a></li>
            <li><a href="add_new_specimen.php?project=<?= $project?>&core=<?= $core?>&sample=<?= $sample?>"><i class="fa fa-plus"></i> Add New Specimen</a></li>
        </ul>
    </nav>
    <?php
// Project Side Bar (Needs to be updated dynamically from DB)
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
                $db->selectRows("projects", null, "project_name", "project_name", true);
                foreach ($db->recordsArray() as $project) {
                    echo "<a href=\"#".$project["project_name"]."\" data-toggle=\"collapse\"><i class=\"fas fa-folder\"></i>  ".$project["project_name"]."</a>
                            <ul id=\"".$project["project_name"]."\" class=\"list-unstyled collapse\">
                            <li><a href='add_new_project.php?edit=".$project["project_name"]."'><i class=\"fa fa-edit\"></i> Edit Project</a></li>
                            <li><a href=\"add_new_core.php?project=".$project["project_name"]."\"><i class=\"fa fa-plus\"></i> Add New Core</a></li>";

                    $db->selectRows("cores", array("project_name" => Mysql::SQLValue($project["project_name"])), "core_id", "core_id", true);
                    foreach ($db->recordsArray() as $core) {
                        echo "<a href=\"#".$core["core_id"]."\" data-toggle=\"collapse\"><i class=\"fa fa-database\"></i> ".$core["core_id"]."</a>
                        <ul id=\"".$core["core_id"]."\" class=\"list-unstyled collapse\">
                        <li><a href='add_new_core.php?project=".$project["project_name"]."&edit=".$core["core_id"]."'><i class=\"fa fa-edit\"></i> Edit Core</a></li>
                        <li><a href=\"add_new_sample.php?project=".$project["project_name"]."&core=".$core["core_id"]."\" data-parent=\"#".$core["core_id"]."\"><i class=\"fa fa-plus\"></i> Add New Sample</a></li>";

                        $db->selectRows("samples", array("core_id" => Mysql::SQLValue($core["core_id"])), "sample_id", "sample_id", true);
                        foreach ($db->recordsArray() as $sample) {
                            echo "<li><a href=\"sample.php?project=".$project["project_name"]."&core=".$core["core_id"]."&sample=".$sample["sample_id"]."\"><i class=\"fa fa-flask\"></i> ".$sample["sample_id"]."</a></li>";
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