<?php
use phpformbuilder\database\Mysql;
?>

<style>
    .subsubdp {
        padding-left: 10%;
    }
    #sidebar {
        overflow: hidden;
        z-index: 3;
    }
    #sidebar .list-group {
        min-width: 400px;
        background-color: #333;
        min-height: 100vh;
    }
    #sidebar i {
        margin-right: 6px;
    }

    #sidebar .list-group-item {
        border-radius: 0;
        background-color: #333;
        color: #ccc;
        border-left: 0;
        border-right: 0;
        border-color: #2c2c2c;
        white-space: nowrap;
    }

    /* highlight active menu */
    #sidebar .list-group-item:not(.collapsed) {
        background-color: #222;
    }

    /* closed state */
    #sidebar .list-group .list-group-item[aria-expanded="false"]::after {
        content: " \f0d7";
        font-family: FontAwesome;
        display: inline;
        text-align: right;
        padding-left: 5px;
    }

    /* open state */
    #sidebar .list-group .list-group-item[aria-expanded="true"] {
        background-color: #222;
    }
    #sidebar .list-group .list-group-item[aria-expanded="true"]::after {
        content: " \f0da";
        font-family: FontAwesome;
        display: inline;
        text-align: right;
        padding-left: 5px;
    }

    /* level 1*/
    #sidebar .list-group .collapse .list-group-item,
    #sidebar .list-group .collapsing .list-group-item  {
        padding-left: 20px;
    }

    /* level 2*/
    #sidebar .list-group .collapse > .collapse .list-group-item,
    #sidebar .list-group .collapse > .collapsing .list-group-item {
        padding-left: 30px;
    }

    /* level 3*/
    #sidebar .list-group .collapse > .collapse > .collapse .list-group-item {
        padding-left: 40px;
    }

    @media (max-width:768px) {
        #sidebar {
            min-width: 35px;
            max-width: 40px;
            overflow-y: auto;
            overflow-x: visible;
            transition: all 0.25s ease;
            transform: translateX(-45px);
            position: fixed;
        }

        #sidebar.show {
            transform: translateX(0);
        }

        #sidebar::-webkit-scrollbar{ width: 0px; }

        #sidebar, #sidebar .list-group {
            min-width: 35px;
            overflow: visible;
        }
        /* overlay sub levels on small screens */
        #sidebar .list-group .collapse.show, #sidebar .list-group .collapsing {
            position: relative;
            z-index: 1;
            width: 190px;
            top: 0;
        }
        #sidebar .list-group > .list-group-item {
            text-align: center;
            padding: .75rem .5rem;
        }
        /* hide caret icons of top level when collapsed */
        #sidebar .list-group > .list-group-item[aria-expanded="true"]::after,
        #sidebar .list-group > .list-group-item[aria-expanded="false"]::after {
            display:none;
        }
    }

    .collapse.show {
        visibility: visible;
    }
    .collapsing {
        visibility: visible;
        height: 0;
        -webkit-transition-property: height, visibility;
        transition-property: height, visibility;
        -webkit-transition-timing-function: ease-out;
        transition-timing-function: ease-out;
    }
    .collapsing.width {
        -webkit-transition-property: width, visibility;
        transition-property: width, visibility;
        width: 0;
        height: 100%;
        -webkit-transition-timing-function: ease-out;
        transition-timing-function: ease-out;
    }
</style>
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
            <li><a href="sample.php?project=<?= $project?>&core=<?= $core?>&sample=<?= $sample?>"><i class="fa fa-stopwatch"></i> Sample Count</a></li>
            <li><a href="search_specimen.php?project=<?= $project?>&core=<?= $core?>&sample=<?= $sample?>"><i class="fa fa-search"></i> Search Specimen</a></li>
            <li><a href="add_new_specimen.php?project=<?= $project?>&core=<?= $core?>&sample=<?= $sample?>"><i class="fa fa-syringe"></i> Add New Specimen</a></li>
        </ul>
    </nav>
    <?php
// Project Side Bar (Needs to be updated dynamically)
} else {
    ?>
    <div class="col-md-3 float-left col-1 pl-0 pr-0 collapse width show" id="sidebar">
        <div class="list-group border-0 card text-center text-md-left">
            <a href="index.php" class="list-group-item d-inline-block collapsed"><i class="fa fa-home"></i> <span class="d-none d-md-inline">Home</span></a>
            <a href="add_new_project.php" class="list-group-item d-inline-block collapsed"><i class="fa fa-plus"></i> <span class="d-none d-md-inline">Add New Project</span></a>
            <?php
            $db = new Mysql();
            $db->selectRows("projects", null, "project_name", "project_name", true);
            foreach ($db->recordsArray() as $project) {
                echo "<a href=\"#" . $project["project_name"] . "\" class=\"list-group-item d-inline-block collapsed\" data-toggle=\"collapse\" aria-expanded=\"false\"><i class=\"fas fa-suitcase\"></i>  <span class=\"d-none d-md-inline\">" . $project["project_name"] . "</span></a>
                        <div class=\"collapse\" id=\"" . $project["project_name"] . "\" data-parent=\"#sidebar\">
                            <a href=\"add_new_core.php?project=" . $project["project_name"] . "\" data-parent=\"#" . $project["project_name"] . "\"><i class=\"fa fa-plus\"></i> Add New Core</a>";

                $db->selectRows("cores", array("project_name" => Mysql::SQLValue($project["project_name"])), "core_id", "core_id", true);
                foreach ($db->recordsArray() as $core) {
                    echo "<a href=\"#" . $core["core_id"] . "\" class=\"list-group-item\" data-toggle=\"collapse\" aria-expanded=\"false\"><i class=\"fas fa-folder\"></i>  " . $core["core_id"] . "</a>
                        <div class=\"collapse\" id=\"" . $core["core_id"] . "\" data-parent=\"#" . $project["project_name"] . "\">
                            <a href=\"add_new_core.php?project=" . $core["core_id"] . "\" data-parent=\"#" . $core["core_id"] . "\"><i class=\"fa fa-plus\"></i> Add New Sample</a>";

                    $db->selectRows("samples", array("core_id" => Mysql::SQLValue($core["core_id"])), "sample_id", "sample_id", true);
                    foreach ($db->recordsArray() as $sample) {
                        echo "<a href=\"sample.php?project=" . $project["project_name"] . "&core=" . $core["core_id"] . "&sample=" . $sample["sample_id"] . "\" data-parent=\"#" . $core["core_id"] . "\"><i class=\"fa fa-bolt\"></i> " . $sample["sample_id"] . "</a>";
                    }
                    echo "</div>";
                }

                echo "</div>";
            }
            ?>

        </div>
    </div>
    <?php
}