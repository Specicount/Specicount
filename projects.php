<?php
use phpformbuilder\database\Mysql;

require_once 'classes/Page_Renderer.php';

function getTable(){

    $db = new Mysql();
    $email = Mysql::SQLValue($_SESSION['email']);
    $sql = "SELECT project_id, access_level FROM user_project_access NATURAL JOIN projects WHERE email =".$email." ORDER BY project_id";
    $db->query($sql);

    if(empty($db)){return '<p style="font-style: italic">Create a project to get started</p>';}

    $output= "
    <style>
    .project-list a, .project-list text {
        color: inherit;
        padding:8px;
        width: 100%;
        height: 100%;
        display:inline-block;
        text-decoration: none;
    }
    .project-list a:hover {
        background-color: #cccccc;
    }
    .project-list table {
        border-collapse: collapse;
        color: #343a40;
        width: 100%;
    }
    .project-list th, .project-list td{
        border: 1px solid #343a40;
        padding: 0px;
        vertical-align:top;
    }
    .project-list tr:nth-child(even){
        background-color: #eeeeee;
    }
    .internal-list table, .internal-list td {
        border-collapse: collapse;
        background-color: inherit;
        border: 0px;
        width: 100%;
    }
    .internal-list tr:not(:first-child) {
        border-top: 1px solid #cccccc;
    }
    </style>
    <div>  
    <table class='project-list' style='width: 100%'>
    <tr><th style='width: 30%'><text>Name</text></th>
        <th style='width: 30%'><text>No. Cores</text></th>
        <th style='width: 20%'><text>No. Samples</text></th>
        <th style='width: 20%'><text>No. Specimens</text></th>
        <th style='width: 20%'><text>Access Level</text></th></tr>
    ";

    foreach ($db->recordsArray() as $project_array) {
        $project_id = $project_array["project_id"];
        $project_id_sql = Mysql::SQLValue($project_id);
        #$db->selectRows("cores", array("project_id" => Mysql::SQLValue($project_array["project_id"])), "core_id", "core_id", true);
        $num_cores = $db->querySingleValue("SELECT COUNT(*) FROM cores WHERE project_id=".$project_id_sql);
        $num_samples = $db->querySingleValue("SELECT COUNT(*) FROM samples WHERE project_id=".$project_id_sql);
        $num_specimens = $db->querySingleValue("SELECT COUNT(*) FROM specimens WHERE project_id=".$project_id_sql);
//        $db->query("SELECT cores.core_id, COUNT(samples.sample_id) AS cnt FROM cores LEFT JOIN samples ON cores.core_id = samples.core_id WHERE cores.project_id =".$project_id_sql." GROUP BY cores.core_id ORDER BY cores.core_id");

        $output .= "<tr>";
        $output .= "<td><a href='?project_id=".$project_id."'>".$project_id."</a></td>";
        $output .= "<td><text>".$num_cores."</text></td>";
        $output .= "<td><text>".$num_samples."</text></td>";
        $output .= "<td><text>".$num_specimens."</text></td>";
        $output .= "<td><text>".$project_array["access_level"]."</text></td>";
        $output .= "</tr>";
    }
    $output .= "</table></div>";
    return $output;
}

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setPageTitle("Projects Home");

$page_render->setInnerHTML(getTable());
$page_render->renderPage();