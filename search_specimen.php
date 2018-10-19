<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;
use classes\Post_Form;

/* =============================================
    start session and include form class
============================================= */

require_once $_SERVER["DOCUMENT_ROOT"]."/page-components/functions.php";

include_once 'phpformbuilder/Form.php';
require_once 'classes/Post_Form.php';
require_once 'phpformbuilder/database/db-connect.php';
require_once 'phpformbuilder/database/Mysql.php';
require_once "classes/Page_Renderer.php";

/* =============================================
    validation if posted
============================================= */

class Search_Form extends Post_Form {

    protected function setRequiredAccessLevelsForPost() {
        // Allow all project members to post
        $this->post_required_access_levels = array("owner","admin","collaborator","visitor");
    }

    protected function registerPostActions() {
        $this->registerPostAction("search", true, false);
        $this->registerPostAction("addToSample", isset($_POST["add-to-sample-btn"]), false);
    }

    protected function addToSample() {
        // -------- VALIDATION --------
        $my_access_level = getAccessLevel();
        // Make sure that only the owner can transfer ownership
        if ($my_access_level == "visitor") {
            storeErrorMsg("You cannot do that as a visitor");
            return;
        }

        // -------- ADD TO SAMPLE --------
        $specimen_pkeys = explode("~", $_POST["add-to-sample-btn"]);
        $specimen_project_id = $specimen_pkeys[0];
        $specimen_id = $specimen_pkeys[1];

        $update["project_id"] = Mysql::SQLValue($_GET["project_id"]);
        $update["core_id"] = Mysql::SQLValue($_GET["core_id"]);
        $update["sample_id"] = Mysql::SQLValue($_GET["sample_id"]);
        $update_curve = $update; // Used for concentration curve
        $update["specimen_project_id"] = Mysql::SQLValue($specimen_project_id);
        $update["specimen_id"] = Mysql::SQLValue($specimen_id);
        $update["count"] = Mysql::SQLValue(1);
        $this->db->insertRow($this->getTableName(), $update);
        if (!empty($this->db->error())) {
            if (stripos($this->db->error(), "Duplicate") !== false) {
                storeErrorMsg("Specimen $specimen_id already added to sample");
            } else {
                storeDbMsg($this->db);
            }
        } else {
            # Do concentration curve
            $where_clause = Mysql::buildSQLWhereClause($update_curve);
            $this->db->query("SELECT SUM(count) as total FROM found_specimens ".$where_clause);
            $tally_count = $this->db->recordsArray()[0]["total"]; // Total tally count for all specimens in the sample
            $update_curve["tally_count"] = Mysql::SQLValue($tally_count, "int");
            $this->db->query("SELECT COUNT(*) as amount FROM found_specimens ".$where_clause);
            $unique_spec = $this->db->recordsArray()[0]["amount"]; // Number of unique specimens in the sample
            $update_curve["unique_spec"] = Mysql::SQLValue($unique_spec, "int");
            $this->db->insertRow('concentration_curve', $update_curve);
            storeDbMsg($this->db,"Successfully added specimen $specimen_id to sample!");
        }
    }

    protected function search() {
        $search = trim($_POST["search-input"]);
        $search = explode(" ", $search);
        $search = array_map("trim", $search);
        $search = array_unique($search);

        $columns = $this->db->getColumnNames("specimens");
        $col = array();
        foreach ($search as $s) {
            if (!empty($s)) {
                $s = Mysql::SQLValue($s);
                $query = array();
                if (strpos($s, "*") !== false) {
                    $s = str_replace("*", "%", $s);
                    $q = "LIKE $s";
                } else {
                    $q = "= $s";
                }
                foreach ($columns as $name) {
                    $query[] = "$name " . $q;
                }
                $col[] = "(" . implode(" OR ", $query) . ")";
            }
        }

        $my_email = Mysql::sqlValue($_SESSION["email"]);
        $this_project_id = Mysql::sqlValue($_GET["project_id"]);
        $sql = "";
        switch ($_POST["project-filter"]) {
            case "all": $sql = "SELECT DISTINCT * FROM
                                (SELECT project_id FROM user_project_access WHERE email=$my_email) t1
                                UNION
                                (SELECT project_id FROM projects WHERE is_global=TRUE)"; break;
            case "this-project": $sql = "SELECT project_id FROM projects WHERE project_id=$this_project_id"; break;
            case "my-projects": $sql = "SELECT project_id FROM user_project_access WHERE email=$my_email AND access_level='owner'"; break;
            case "shared-projects": $sql = "SELECT project_id FROM user_project_access WHERE email=$my_email AND access_level<>'owner'"; break;
            case "global-projects": $sql = "SELECT project_id FROM projects WHERE is_global=TRUE"; break;
            case "global-reference-specimens": $sql = "SELECT project_id FROM projects WHERE project_id='Global Reference Specimens'"; break;
        }
        $this->db->query($sql);
        $projects = $this->db->recordsArray();
        if ($projects) {
            foreach($projects as $project) {
                $project_ids[] = Mysql::sqlValue($project["project_id"]);
            }
            $project_ids = " AND project_id IN (".implode(",",$project_ids).")";
            $sql = "SELECT * FROM BioBase.specimens WHERE " .implode(" AND ", $col).$project_ids;
            $this->db->query($sql);
            if ($this->db->rowCount() > 0) {
                global $results;
                $results = $this->db->recordsArray();
            }
        }

        //print_r($project_ids);

//        $sql = "SELECT project_id FROM projects NATURAL JOIN user_project_access NATURAL JOIN users WHERE is_global=TRUE AND "; //Trusted global projects ???



    }
}


/* ==================================================
    The Form
================================================== */

$form = new Search_Form('search-form', "found_specimens", 'vertical', 'novalidate', 'bs4');

// Since form is vertical it won't automatically print elements in a row
$options = array(
    'elementsWrapper' => '<div class="form-group row justify-content-end"></div>',
);
$form->setOptions($options);
$form->groupInputs("search-input","project-filter", "search-btn");
$form->addInputWrapper('<div class="col-sm-6"></div>', "search-input");
$form->addInputWrapper('<div class="col-sm-3"></div>', "project-filter");
$form->addInputWrapper('<div class="col-sm-3"></div>', "search-btn");

$form->setCols(0, 6);
$form->addInput('text', 'search-input', '', '', 'placeholder=Search Attributes Here...');
$form->addHelper('Project Filter', 'project-filter');
$form->addOption("project-filter", "this-project", "This project");
$form->addOption("project-filter", "my-projects", "My projects");
$form->addOption("project-filter", "shared-projects", "Shared projects");
$form->addOption("project-filter", "global-projects", "Global projects");
$form->addOption("project-filter", "global-reference-specimens", "Global reference specimens");
$form->addOption("project-filter", "all", "All");
$form->setCols(0, 3);
$form->addSelect("project-filter", '', 'required');
$form->setCols(0, 3);
$form->addBtn('submit', 'search-btn', 1, '<i class="fa fa-search" aria-hidden="true"></i> Search', 'class=btn btn-success ladda-button, data-style=zoom-in');

$form->setCols(0, 6);

$options = array(
    'elementsWrapper' => '<div class="form-group"></div>',
);
$form->setOptions($options);

# Add in results
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($results)) {
        $form->addHtml('<div class="square-grid">');
        foreach ($results as $specimen) {
            $specimen_pkeys = $specimen["project_id"].'~'.$specimen["specimen_id"];
            $image = $specimen["image_folder"].$specimen["primary_image"];
            $form->addHtml('<div class="specimen-container cell"');
            if (is_file($image)) {
                $form->addHtml(' style="background-image:url(\'/phpformbuilder/images/uploads/'.$specimen["project_id"].'/'.$specimen["specimen_id"].'/'.$specimen["primary_image"].'\');"');
            }
            $form->addHtml('>');
            $form->addHtml('<div class="counter"><p class="counter-text">ID: ' . $specimen["specimen_id"] . '</p></div>');
            $form->addHtml('<div class="overlay">');
            $form->addHtml('<text>ID: ' . $specimen["specimen_id"] . '</text>');
            $form->addHtml('<a href="#"><span><i class="fas fa-window-close top-right-btn close"></i></span></a>');
            $my_access_level = getAccessLevel();
            if ($my_access_level != "visitor") {
                $form->addHtml('<a href="add_new_specimen.php?edit=true&project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-edit bot-left-btn"></i></a>');
                if (isset($_GET["sample_id"])) {
                    $form->addBtn('submit', 'add-to-sample-btn', $specimen_pkeys, 'Add To Sample <i class="fa fa-plus-circle" aria-hidden="true"></i>', 'class=btn btn-success ladda-button mid-btn, data-style=zoom-in');
                }
                $form->addHtml('<a href="specimen_details.php?project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-info-circle bot-right-btn"></i></a>');
            } else {
                $form->addHtml('<a href="specimen_details.php?project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-info-circle bot-right-btn"></i></a>');
            }
            $form->addHtml('</div>');
            $form->addHtml('</div>');
        }
        $form->addHtml('</div><br><br>');
    } else {
        $form->addHtml('<p class="alert alert-danger col-sm-10">No specimen found</p>');
    }
}



// jQuery validation

$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');

$title = $_GET['project_id']." > ". $_GET['core_id']. " > ".$_GET['sample_id']." > Search Sample";
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageRestrictions(true, true, false, false);
$page_render->setPageTitle($title);
$page_render->renderPage();