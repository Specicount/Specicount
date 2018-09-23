<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;
use classes\Post_Form;
use function functions\getAccessLevel;

/* =============================================
    start session and include form class
============================================= */

require_once "classes/Page_Renderer.php";
require_once "classes/Post_Form.php";

class Sample_Count_Form extends Post_Form {
    protected function setRequiredAccessLevelsForPost() {
        $this->post_required_access_levels = array("owner","admin","collaborator","visitor");
    }

    protected function registerPostActions() {
        $this->registerPostAction("delete", isset($_POST["delete-btn"]), false);
        $this->registerPostAction("export", isset($_POST["export-btn"]), false);
        $this->registerPostAction("save", isset($_POST["save-btn"]));
        $this->registerPostAction("saveReorder", isset($_POST["save-reorder-btn"]));
    }

    protected function delete() {
        // -------- VALIDATION --------
        $my_access_level = getAccessLevel();
        if ($my_access_level == "visitor") {
            $this->storeErrorMsg("You cannot do that as visitor");
            return;
        }

        // -------- DELETE --------
        $specimen_pkeys = explode(',',trim(base64_decode(str_replace("-", "=", $_POST["delete"]))));
        $specimen_project_id = $specimen_pkeys[0];
        $specimen_id = $specimen_pkeys[1];


        $this->filter["specimen_project_id"] = Mysql::SQLValue($specimen_project_id);
        $this->filter["specimen_id"] = Mysql::SQLValue($specimen_id);
        $this->db->deleteRows("found_specimens", $this->filter);
        $this->storeDbMsg("Successfully deleted specimen " . $specimen_id . " from sample");
    }

    protected function export() {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="sample_export_'.date("Ymd").'.csv";');

        $sql =  "SELECT  s.specimen_id, s.project_id as s_project_id, s.family, s.genus, s.species, s.plant_function_type, ".
                "fs.sample_id, fs.core_id, fs.project_id, fs.count  ".
                "FROM specimens AS s JOIN found_specimens as fs ".
                "ON s.specimen_id = fs.specimen_id AND s.project_id = fs.specimen_project_id ".
                "HAVING sample_id = ".$this->filter["sample_id"]." AND core_id = ".$this->filter["core_id"]." AND project_id = ".$this->filter["project_id"]." ".
                "ORDER BY `order` DESC";
        $this->db->query($sql);
        $specimen_data = $this->db->recordsArray();

        $this->db->selectRows($this->table_name, $this->filter);
        $sample_data = $this->db->recordsArray()[0];

        // Create a PHP output stream for the user to download
        $f = fopen('php://output', 'w');
        fputcsv($f, array("Specimen", "Family", "Genus", "Species", "Plan Function Type", "Count"), ",");
        fputcsv($f, array("Lycopodium", "", "", "", "", $sample_data["lycopodium"]), ",");
        fputcsv($f, array("Charcoal", "", "", "", "", $sample_data["charcoal"]), ",");

        foreach ($specimen_data as $spec) {
            fputcsv($f, array($spec["specimen_id"], $spec["family"], $spec["genus"], $spec["species"], $spec["plant_function_type"], $spec["count"]), ",");
        }

        // Refresh page back to what it was before
        header("Refresh:0");
        exit;
    }

    protected function save() {
        // -------- VALIDATION --------
        $my_access_level = getAccessLevel();
        if ($my_access_level == "visitor") {
            $this->storeErrorMsg("You cannot do that as visitor");
            return;
        }

        // -------- SAVE --------
        $sample_data = $this->db->selectRows("samples", $this->filter);
        // Concurrency control - if this sample was last updated by this device
        if ($sample_data["last_edit"] == $_POST["last_edit"] || empty($sample_data["last_edit"])) {
            $specimens = array_slice($_POST, 6); // Skip the first 6 post variables to get to specimens
            foreach ($specimens as $specimen => $count) {
                $specimen_pkeys = explode(',',trim(base64_decode(str_replace("-", "=", $specimen))));
                $specimen_project_id = $specimen_pkeys[0];
                $specimen_id = $specimen_pkeys[1];

                $filter = $this->filter;
                $filter["specimen_project_id"] = Mysql::SQLValue($specimen_project_id);
                $filter["specimen_id"] = Mysql::SQLValue($specimen_id);

                $update["count"] = Mysql::SQLValue($count);

                $this->db->updateRows("found_specimens", $update, $filter);
            }
            $update_sample["last_edit"] = Mysql::SQLValue(date("Y-m-d H:i:s"));
            $update_sample["lycopodium"] = Mysql::SQLValue($_POST["lycopodium"]);
            $update_sample["charcoal"] = Mysql::SQLValue($_POST["charcoal"]);
            $this->db->updateRows($this->table_name, $update_sample, $this->filter);
            $this->storeDbMsg("Successfully updated sample!");
        } else {
            $this->storeErrorMsg("Updated by another device, please reload the page again and try again. Warning: You will lose all your progress since your last save");
        }
    }

    protected function saveReorder() {
        // -------- VALIDATION --------
        $my_access_level = getAccessLevel();
        if ($my_access_level == "visitor") {
            $this->storeErrorMsg("You cannot do that as visitor");
            return;
        }

        // -------- SAVE REORDER --------
        $this->update();
        $where_clause = Mysql::buildSQLWhereClause($this->filter);
        $sql = "UPDATE BioBase.found_specimens SET `order` = `count` ".$where_clause;
        $this->db->query($sql);
        $this->storeDbMsg("Successfully updated and reordered the sample!");
    }

}


/* ==================================================
    The Form
================================================== */

$form = new Sample_Count_Form("sample-count", "samples", 'vertical', 'class=mb-5, novalidate', 'bs4');

#######################
# Sample grid
#######################
$form->addInput('hidden','last_edit');
$form->addHtml("<div class='row'>");
$form->addHtml("<div class='col-sm'>");

#######################
# Clear/Save
#######################
$form->addBtn('submit', 'save-btn', 1, 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success, data-style=zoom-in', 'my-btn-group');
$form->addBtn('button', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onClick=reload()', 'my-btn-group');
$form->addBtn('submit', 'export-btn', "export", 'Export <i class="fa fa-download append" aria-hidden="true"></i>', 'class=btn btn-info', 'my-btn-group');
$form->addBtn('submit', 'save-reorder-btn', 1, 'Reorder and Save <i class="fa fa-sync append" aria-hidden="true"></i>', 'class=btn btn-success', 'my-btn-group');
$form->printBtnGroup('my-btn-group');

#############################
# Lycopodium/Charcoal Counts
#############################
$db = new Mysql();
$db->selectRows($form->getTableName(), $form->getFilterArray());
$sample = $db->recordsArray()[0];
$lycopodium_count = $sample["lycopodium"];
$charcoal_count = $sample["charcoal"];

#######################
# Lycopodium
#######################
$form->addHtml('<div style="display:inline-block; max-width:200px; padding-right:10px;">');
$form->addHtml('<text style="font-weight: bold;padding: 5px">Lycopodium</text>');
$form->addHtml("<table style='width: 100%'>");
$form->addHtml("<tr style=\"vertical-align:top\"><td style='text-align: left'>");
$form->addBtn('button', 'lycopodium_subtract', 1, '<i class="fa fa-minus" aria-hidden="true"></i>', 'class=btn btn-success sp_count, data-style=zoom-in, onclick=subtract(\'lycopodium\')');
$form->addHtml("</td><td>");
$_SESSION[$form->getFormName()]["lycopodium"] = $lycopodium_count; //Fill in the lycopodium input with database count
$form->addInput('number', 'lycopodium', '', '', 'required');
$form->addHtml("</td><td style='text-align: right'>");
$form->addBtn('button', 'lycopodium_add', 1, '<i class="fa fa-plus" aria-hidden="true"></i>', 'class=btn btn-success, data-style=zoom-in, onclick=add(\'lycopodium\')');
$form->addHtml("</td></tr></table>");
$form->addHtml('</div>');

#######################
# Charcoal
#######################
$form->addHtml('<div style="display:inline-block; max-width:200px; padding-right:10px;">');
$form->addHtml('<text style="font-weight: bold;padding: 5px">Charcoal</text>');
$form->addHtml("<table style='width: 100%'>");
$form->addHtml("<tr style=\"vertical-align:top\"><td style='text-align: left'>");
$form->addBtn('button', 'charcoal_subtract', 1, '<i class="fa fa-minus" aria-hidden="true"></i>', 'class=btn btn-success sp_count, data-style=zoom-in, onclick=subtract(\'charcoal\')');
$form->addHtml("</td><td>");
$_SESSION[$form->getFormName()]["charcoal"] = $charcoal_count; //Fill in the charcoal input with database count
$form->addInput('number', 'charcoal', '', '', 'required');
$form->addHtml("</td><td style='text-align: right'>");
$form->addBtn('button', 'charcoal_add', 1, '<i class="fa fa-plus" aria-hidden="true"></i>', 'class=btn btn-success, data-style=zoom-in, onclick=add(\'charcoal\')');
$form->addHtml("</td></tr></table>");
$form->addHtml('</div>');

$form->addHtml("</div><div class='col-sm'>");

// Concentration curve div
$form->addHtml("<div style='height: 350px' id=\"chart_div\"></div><br/>");

$form->addHtml("</div></div>");

$db = new Mysql();

$filter = $form->getFilterArray();
$sql =  "SELECT s.specimen_id, s.project_id as specimen_project_id, s.image_folder, s.primary_image, fs.sample_id, fs.core_id, fs.project_id, fs.count ".
        "FROM specimens AS s JOIN found_specimens as fs ".
        "ON s.specimen_id = fs.specimen_id AND s.project_id = fs.specimen_project_id ".
        "HAVING sample_id = ".$filter["sample_id"]." AND core_id = ".$filter["core_id"]." AND project_id = ".$filter["project_id"]." ".
        "ORDER BY `order` DESC";
$db->query($sql);
$specimen_data = $db->recordsArray();

#######################
# Specimen Grid
#######################
if($db->rowCount() > 0) {

    //$form->addHtml('<hr>');

    $form->addHtml('<div class="square-grid">');

    foreach ($specimen_data as $specimen) {
        $specimen_pkeys_encoded = str_replace("=", "-", trim(base64_encode($specimen["specimen_project_id"].','.$specimen["specimen_id"])));
        $image = $specimen["image_folder"].$specimen["primary_image"];
        $form->addHtml('<div id="'.$specimen_pkeys_encoded.'_container" class="specimen-container cell"');
        if (is_file($image)) {
            $form->addHtml(' style="background-image:url(\'/phpformbuilder/images/uploads/'.$specimen["specimen_id"].'/'.$specimen["primary_image"].'\');"');
        }
        $form->addHtml('>');
        //$form->addHtml('<div id="'.$specimen["specimen_id"].'_imageblur" class="imageblur" style="background-image:url(\'/phpformbuilder/images/uploads/'.$specimen_name.'/'.$specimen["primary_image"].'\');"></div>');
        $form->addHtml('<div id="'.$specimen_pkeys_encoded.'_counter" class="counter"><p id="'.$specimen_pkeys_encoded.'_counter_text">' . $specimen["count"] . '</p></div>');
        $form->addHtml('<div id="'.$specimen_pkeys_encoded.'_overlay" class="overlay">');
        $form->addHtml('<text>ID: ' . $specimen["specimen_id"] . '</text>');
        $form->addHtml('<a href="add_new_specimen.php?edit=true&project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-edit edit-btn"></i></a>');
        $form->addHtml('<a href="specimen_details.php?project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'" target="_blank"><i class="fa fa-info-circle del-btn"></i></a>');
        $form->addHtml('<a href="#"><span><i id="'.$specimen_pkeys_encoded.'_close" class="fas fa-window-close close-btn"></i></span></a>');
        $form->addBtn('button', 'add-to-count', 1, '<i class="fa fa-plus"></i>', 'class=btn btn-success add-btn, data-style=zoom-in, onclick=add(\''.$specimen_pkeys_encoded.'\');updateCounter(\''.$specimen_pkeys_encoded.'\')');
        $form->addBtn('submit', 'delete-btn', $specimen_pkeys_encoded, ' <i class="fa fa-trash"></i>', 'class=btn btn-danger del-btn, data-style=zoom-in, onclick=return confirm(\'Are you sure you want to delete this specimen from the sample?\')');
        $form->addInput('number', $specimen_pkeys_encoded, $specimen["count"], '', 'required onchange=updateCounter(\''.$specimen_pkeys_encoded.'\')');
        $form->addHtml('</div>');
        $form->addHtml('</div>');
    }
    $form->addHtml('</div><br><br>');

} else {
    $form->addHtml('<p style="font-style: italic; margin-top:-200px;">No specimens added to this sample</p>');
}

// jQuery validation
$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle($_GET['project_id']." > ". $_GET['core_id']. " > ".$_GET['sample_id']." > Sample Count");
$page_render->renderPage();

// Add concentration curve scripts
require_once "concentration.php";
?>
<script>

    // Undo changes for the sample (only if not previously saved)
    function reload(){
        if (confirm('Are you sure you want to reset the sample?')) {
            window.location.reload();
        }
    }

    // Update counter text on hover
    function updateCounter(specimen_id){
        document.getElementById(specimen_id+"_counter_text").innerHTML = document.getElementById(specimen_id).value;
    }

    // Add to counter
    function add(specimen_id) {
        //console.log("value of "+specimen_id+" input is "+document.getElementById(specimen_id).value);
        document.getElementById(specimen_id).value = parseInt(document.getElementById(specimen_id).value) + 1;
    }

    // Subtract from counter
    function subtract(specimen_id){
        document.getElementById(specimen_id).value = parseFloat(document.getElementById(specimen_id).value) - 1;
    }

    // Enable key presses for counter
    window.onkeyup = function(e) {
        // noinspection JSAnnotator
        let key = e.keyCode ? e.keyCode : e.which;
        <?php
        $keys = str_split("qwertyuiopasdfghjklzxcvbnm"); // hotkeys
        $i = 0;
        foreach ($keys as $k) {
            $specimen_id = str_replace("=", "-", trim(base64_encode($specimen_data[$i]["specimen_id"])));
            echo "if (key == ".(ord($k) - 32).") {
                    add('".$specimen_id."');
                    updateCounter('".$specimen_id."');
                  }";
            $i++;
            if ($i >= count($specimen_data)) break;
        }
        ?>
    };
</script>
