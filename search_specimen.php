<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;
use classes\Post_Form;
use function functions\getAccessLevel;

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
            $this->storeErrorMsg("You cannot do that as a visitor");
            return;
        }

        // -------- ADD TO SAMPLE --------
        $primary_key_values = explode(",", $_POST["add-to-sample-btn"]);
        $specimen_project_id = trim(base64_decode(str_replace("-", "=", $primary_key_values[0])));
        $specimen_id = trim(base64_decode(str_replace("-", "=", $primary_key_values[1])));

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
                $this->storeErrorMsg("Specimen $specimen_id already added to sample");
            } else {
                $this->storeDbMsg();
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
            $this->storeDbMsg("Successfully added specimen $specimen_id to sample!");
        }
    }

    protected function search() {
        global $results;
        $results = array();
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

        // TODO: Implement search filters
        $project_id_sql = Mysql::SQLValue($_GET["project_id"]);

        $sql = "SELECT * FROM BioBase.specimens WHERE " . implode(" AND ", $col); // . " AND project_id=". $project_id_sql;
        $this->db->query($sql);
        if ($this->db->rowCount() > 0) {
            $results = $this->db->recordsArray();
        }
    }
}

/* ==================================================
    The Form
================================================== */

$form = new Search_Form('search-form', "found_specimens", 'vertical', 'class=mb-5, novalidate', 'bs4');
$options = array(
    'elementsWrapper' => '<div class="input-group"></div>'
);
$form->setOptions($options);
$form->groupInputs('search-input', 'search-btn');
$form->addInputWrapper('<span class="input-group-btn"></span>', 'search-btn');
$form->addInput('text', 'search-input', '', '', 'placeholder=Search Attributes Here ...');
$form->addBtn('submit', 'search-btn', 1, '<i class="fa fa-search" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in');

$form->addHtml('<br><br>');

# Add in results
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($results)) {
        $form->addHtml('<div class="square-grid">');
        foreach ($results as $specimen) {
            $specimen_project_id_encoded = str_replace("=", "-", trim(base64_encode($specimen["project_id"])));
            $specimen_id_encoded = str_replace("=", "-", trim(base64_encode($specimen["specimen_id"])));
            $primary_key_values = $specimen_project_id_encoded . ',' . $specimen_id_encoded;
            $image = $specimen["image_folder"].$specimen["primary_image"];
            $form->addHtml('<div id="'.$specimen_id_encoded.'" class="specimen-container cell"');
            if (is_file($image)) {
                $form->addHtml(' style="background-image:url(\'/phpformbuilder/images/uploads/'.$specimen["specimen_id"].'/'.$specimen["primary_image"].'\');"');
            }
            $form->addHtml('>');
            $form->addHtml('<div id="'.$specimen_id_encoded.'_counter" class="counter"><p id="'.$specimen_id_encoded.'_counter_text">ID: ' . $specimen["specimen_id"] . '</p></div>');
            $form->addHtml('<div id="'.$specimen_id_encoded.'_overlay" class="overlay">');
            $form->addHtml('<text>ID: ' . $specimen["specimen_id"] . '</text>');
            $form->addHtml('<a href="add_new_specimen.php?edit=true&project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-edit edit-btn"></i></a>');
            $form->addHtml('<a href="specimen_details.php?project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-info-circle del-btn"></i></a>');
            $form->addHtml('<a href="#"><span><i id="'.$specimen_id_encoded.'_close" class="fas fa-window-close close-btn"></i></span></a>');
            $form->addBtn('submit', 'add-to-sample-btn', $primary_key_values, 'Add To Sample <i class="fa fa-plus-circle" aria-hidden="true"></i>', 'class=btn btn-success ladda-button add-btn, data-style=zoom-in');
            $form->addHtml('</div>');
            $form->addHtml('</div>');
        }
        $form->addHtml('</div><br><br>');
    } else {
        $form->addHtml('<p style="text-align: center; color: red">No samples found</p>');
    }
}

// jQuery validation

$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');

$title = "$project_id > $core_id > $sample_id > Search Sample";
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($title);
$page_render->renderPage();
