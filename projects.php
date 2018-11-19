<?php
use phpformbuilder\database\Mysql;

require_once 'classes/Page_Renderer.php';

print_r($_POST);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['export-core-btn'])) {

    }
}

function getTable(){

    $db = new Mysql();
    $email = Mysql::SQLValue($_SESSION['email']);
    $sql = "SELECT project_id, access_level FROM user_project_access NATURAL JOIN projects WHERE email =".$email." ORDER BY project_id";
    $db->query($sql);

    if(empty($db)){return '<p style="font-style: italic">Create a project to get started</p>';}

    $output= "
    <style>
    .table-row{
        cursor:pointer;
    }
    </style>
    <div class='container'>  
    <table class='table table-bordered table-condensed table-striped table-hover' style='width: 100%'>
    <thead>
        <tr>
            <th style='width: 25%'>Name</th>
            <th style='width: 15%'>Access Level</th>
            <th style='width: 15%'>No. Cores</th>
            <th style='width: 15%'>No. Samples</th>
            <th style='width: 15%'>No. Specimens</th>
            <th style='width: 15%'>Last Sample Edit</th>
        </tr>
    </thead>
    <tbody>
    ";

    foreach ($db->recordsArray() as $project_array) {
        $project_id = $project_array["project_id"];
        $project_id_sql = Mysql::SQLValue($project_id);
        #$db->selectRows("cores", array("project_id" => Mysql::SQLValue($project_array["project_id"])), "core_id", "core_id", true);
        $num_cores = $db->querySingleValue("SELECT COUNT(*) FROM cores WHERE project_id=".$project_id_sql);
        $num_samples = $db->querySingleValue("SELECT COUNT(*) FROM samples WHERE project_id=".$project_id_sql);
        $num_specimens = $db->querySingleValue("SELECT COUNT(*) FROM specimens WHERE project_id=".$project_id_sql);
        $db->selectRows("samples", array("project_id"=>$project_id_sql),"last_edit", "last_edit", true);
        $last_sample_edit = $db->recordsArray()[0]["last_edit"];

        $output .= "<tr class='table-row' data-href='?project_id=".$project_id."'>";
        $output .= "<td>".$project_id."</td>";
        $output .= "<td>".$project_array["access_level"]."</td>";
        $output .= "<td>".$num_cores."</td>";
        $output .= "<td>".$num_samples."</td>";
        $output .= "<td>".$num_specimens."</td>";
        $output .= "<td>".$last_sample_edit."</td>";
        $output .= "</tr>";
    }
    $output .= "</tbody></table></div>";

    return $output;


}

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Projects Home");
$page_render->setInnerHTML(getTable());


if (isset($_GET["project_id"])) {
    $page_render->setPageRestrictions(true, true);
} else {
    $page_render->setPageRestrictions(true);
    $page_render->disableSidebar();
}
$page_render->renderPage();
?>
<script>
    $(document).ready(function($) {
        $(".table-row").click(function() {
            window.document.location = $(this).data("href");
        });
    });
</script>
