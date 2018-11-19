<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;
use classes\Post_Form;
use ChartJs\ChartJS;

/* =============================================
    start session and include form class
============================================= */

require_once "classes/Page_Renderer.php";
require_once "classes/Post_Form.php";
require_once 'Chart.js-PHP-master/src/ChartJS.php';

class Sample_Count_Form extends Post_Form {
    protected function setRequiredAccessLevelsForPost() {
        $this->post_required_access_levels = array("owner","admin","collaborator","visitor");
    }

    protected function registerPostActions() {
        $this->registerPostAction("delete", isset($_POST["delete"]), false);
        $this->registerPostAction("export", isset($_POST["export-btn"]), false);
        $this->registerPostAction("save", isset($_POST["save-btn"]));
        $this->registerPostAction("saveReorder", isset($_POST["save-reorder-btn"]));
    }

    protected function delete() {
        $this->save();
        // -------- VALIDATION --------
        $my_access_level = getAccessLevel();
        if ($my_access_level == "visitor") {
            storeErrorMsg("You cannot do that as visitor");
            return;
        }
 
        // -------- DELETE --------
        $specimen_project_id = key($_POST["delete"]);
        $specimen_id = key($_POST["delete"][$specimen_project_id]);

        $this->filter["specimen_project_id"] = Mysql::SQLValue($specimen_project_id);
        $this->filter["specimen_id"] = Mysql::SQLValue($specimen_id);
        $this->db->deleteRows("found_specimens", $this->filter);
        storeDbMsg($this->db,"Successfully deleted specimen " . $specimen_id . " from sample");
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
            storeErrorMsg("You cannot do that as visitor");
            return;
        }

        // -------- SAVE --------
        $sample_data = $this->db->selectRows("samples", $this->filter);
        // Concurrency control - if this sample was last updated by this device
        if ($sample_data["last_edit"] == $_POST["last_edit"] || empty($sample_data["last_edit"])) {
            foreach ($_POST["specimen_counts"] as $specimen_project_id => $specimen_ids) {
                foreach ($specimen_ids as $specimen_id => $count) {
                    $filter = $this->filter;
                    $filter["specimen_project_id"] = Mysql::SQLValue($specimen_project_id);
                    $filter["specimen_id"] = Mysql::SQLValue($specimen_id);
                    $update["count"] = Mysql::SQLValue($count);
                    $this->db->updateRows("found_specimens", $update, $filter);
                    storeDbMsg($this->db);
                }
            }
            $update_sample = array();
            $update_sample["last_edit"] = Mysql::SQLValue(date("Y-m-d H:i:s"));
            $update_sample["lycopodium"] = Mysql::SQLValue($_POST["lycopodium"]);
            $update_sample["charcoal"] = Mysql::SQLValue($_POST["charcoal"]);
            $this->db->updateRows($this->table_name, $update_sample, $this->filter);
            storeDbMsg($this->db,"Successfully updated sample!");
        } else {
            storeErrorMsg("Updated by another device, please reload the page again and try again. Warning: You will lose all your progress since your last save");
        }
    }

    protected function saveReorder() {
        // -------- VALIDATION --------
        $my_access_level = getAccessLevel();
        if ($my_access_level == "visitor") {
            storeErrorMsg("You cannot do that as visitor");
            return;
        }

        // -------- SAVE REORDER --------
        $this->save();
        $where_clause = Mysql::buildSQLWhereClause($this->filter);
        $sql = "UPDATE BioBase.found_specimens SET `order` = `count` ".$where_clause;
        $this->db->query($sql);
        storeDbMsg($this->db,"Successfully updated and reordered the sample!");
    }

}


/* ==================================================
    The Form
================================================== */

unset($_SESSION["sample-count"]);              // This is needed because if a user accesses different pages with the same form_ID in the same session then

$db = new Mysql();
$form = new Sample_Count_Form("sample-count", "samples", 'vertical', 'class=mb-5, novalidate', 'bs4');
$my_access_level = getAccessLevel();
$readonly_attr = "";
if ($my_access_level == "visitor") {
    $readonly_attr = "readonly=readonly, ";
}


#######################
# Sample grid
#######################
$form->addHtml('<style>.input-group-text {padding:0; background-color:transparent; border:none;}</style>');
$form->addInput('hidden','last_edit');

$form->addHtml("<div class='row'>");
$form->addHtml("<div class='col-lg'>");

#######################
# Clear/Save
#######################
if ($my_access_level != "visitor") {
    $form->addBtn('submit', 'save-btn', 1, '<i class="fa fa-save" aria-hidden="true"></i> Save', 'class=btn btn-success, data-style=zoom-in', 'my-btn-group');
    $form->addBtn('button', 'reset-btn', 1, '<i class="fa fa-ban" aria-hidden="true"></i> Reset', 'class=btn btn-warning, onClick=reload()', 'my-btn-group');
    $form->addBtn('submit', 'export-btn', "export", '<i class="fa fa-download append" aria-hidden="true"></i> Export', 'class=btn btn-info', 'my-btn-group');
    $form->addBtn('submit', 'save-reorder-btn', 1, '<i class="fa fa-sync append" aria-hidden="true"></i> Reorder and Save', 'class=btn btn-success', 'my-btn-group');
    $form->addBtn('button', 'stats-btn', 1, '<i style="font-size:30px;" class="fas fa-chart-bar"></i>', 'class=btn, style=box-shadow:none;z-index:0;', 'my-btn-group');
} else {
    $form->addBtn('submit', 'export-btn', "export", '<i class="fa fa-download append" aria-hidden="true"></i> Export', 'class=btn btn-info', 'my-btn-group');
    $form->addBtn('button', 'stats-btn', 1, '<i style="font-size:30px;" class="fas fa-chart-bar"></i>', 'class=btn, style=box-shadow:none;z-index:0;', 'my-btn-group');
}

$form->printBtnGroup('my-btn-group');

#######################
# Concentration curve
#######################
$form->addHtml('<div id="chart_div" style="z-index:10;display:none;">');
$db->selectRows('concentration_curve', $form->getFilterArray());
$curve_data = array();
if (!$db->error()) {
    $table_data = $db->recordsArray();
    foreach ($table_data as $point) {
        $curve_data[] = ['x' => $point["tally_count"], 'y' => $point["unique_spec"]];
    }
} else {
    $curve_data = array();
}

if ($curve_data) {
    $data = [
        'labels' => ['X', 'Y'],
        'datasets' => [[
            'label' => 'Concentration Curve',
            'data' => $curve_data,
            'backgroundColor' => '#28a745',
            'borderColor' => '#28a745',
            'fill' => false
        ]]
    ];

    $options = [
        'responsive' => true,
        'maintainAspectRatio' => false,
        'scales' => [
            'xAxes' => [[
                'type' => 'logarithmic',
                'position' => 'bottom'
            ]]
        ]
    ];

    $attributes = ['id' => 'concentration_curve'];
    $Line = new ChartJS('line', $data, $options, $attributes);

    $form->addHtml($Line);
} else {
    $form->addHtml('No data');
}

$form->addHtml('</div>'); // End chart_div
$form->addHtml('</div>'); // End column
$form->addHtml('</div>'); // End row

#############################
# Lycopodium/Charcoal Counts
#############################
$db->selectRows($form->getTableName(), $form->getFilterArray());
$sample = $db->recordsArray()[0];
$lycopodium_count = $sample["lycopodium"];
$charcoal_count = $sample["charcoal"];

$form->addHtml("<div class='row'>");

#######################
# Lycopodium
#######################

$form->addHtml('<div class="col-sm-3">');
$form->addHtml('<text style="font-weight: bold;padding: 5px">Lycopodium</text>');
if ($my_access_level != "visitor") {
    $form->addIcon("lycopodium",'<button type="button" class="btn btn-success" onclick="subtract(\'lycopodium\')"><i class="fa fa-minus"></i></button>',"before");
    $form->addIcon("lycopodium",'<button type="button" class="btn btn-success" onclick="add(\'lycopodium\')"><i class="fa fa-plus"></i></button>',"after");
}
$_SESSION[$form->getFormName()]["lycopodium"] = $lycopodium_count; //Fill in the lycopodium input with database count
$form->addInput('number', 'lycopodium', '', '', $readonly_attr.'required');
$form->addHtml('</div>');

#######################
# Charcoal
#######################

$form->addHtml('<div class="col-sm-3">');
$form->addHtml('<text style="font-weight: bold;padding: 5px">Charcoal</text>');
if ($my_access_level != "visitor") {
    $form->addIcon("charcoal", '<button type="button" class="btn btn-success" onclick="subtract(\'charcoal\')"><i class="fa fa-minus"></i></button>', "before");
    $form->addIcon("charcoal", '<button type="button" class="btn btn-success" onclick="add(\'charcoal\')"><i class="fa fa-plus"></i></button>', "after");
}
$_SESSION[$form->getFormName()]["charcoal"] = $charcoal_count; //Fill in the charcoal input with database count
$form->addInput('number', 'charcoal', '', '', $readonly_attr.'required');
$form->addHtml('</div>');


$form->addHtml('</div>'); // End row


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
        $specimen_pkeys = $specimen["specimen_project_id"].'~'.$specimen["specimen_id"];
        $image = $specimen["image_folder"].$specimen["primary_image"];
        $form->addHtml('<div class="specimen-container cell"');
        if (is_file($image)) {
            $form->addHtml(' style="background-image:url(\'/phpformbuilder/images/uploads/'.$specimen["specimen_project_id"].'/'.$specimen["specimen_id"].'/'.$specimen["primary_image"].'\');"');
        }
        $form->addHtml('>');
        $form->addHtml('<div class="counter"><p class="counter-text">' . $specimen["count"] . '</p></div>');
        $form->addHtml('<div class="overlay">');
        $form->addHtml('<text>ID: ' . $specimen["specimen_id"] . '</text>');
        $form->addHtml('<a href="#"><span><i class="fas fa-window-close top-right-btn close"></i></span></a>');
        if ($my_access_level != "visitor") {
            $form->addHtml('<a href="add_new_specimen.php?edit=true&project_id='.$specimen["project_id"].'&specimen_id='.$specimen["specimen_id"].'"><i class="fa fa-edit bot-left-btn"></i></a>');
            $form->addBtn('button', 'add-to-count', 1, '<i class="fa fa-plus"></i>', 'class=btn btn-success mid-btn, data-style=zoom-in, onclick=addToSpecimen(this);updateCounter(this)');
            // Delete button must have an array as well - bit ugly :(
            $form->addBtn('submit', 'delete['.$specimen["specimen_project_id"].']['.$specimen["specimen_id"].']', 1, ' <i class="fa fa-trash"></i>', 'class=btn btn-danger bot-right-btn, data-style=zoom-in, onclick=return confirm(\'Are you sure you want to delete this specimen from the sample?\')');
        } else {
            $form->addHtml('<a href="specimen_details.php?project_id='.$specimen["specimen_project_id"].'&specimen_id='.$specimen["specimen_id"].'"><i class="fa fa-info-circle bot-left-btn"></i></a>');
        }

        // Create POST array in the form specimen_counts[project_id][specimen_id]
        $form->addInput('number', 'specimen_counts['.$specimen["specimen_project_id"].']['.$specimen["specimen_id"].']', $specimen["count"], '', $readonly_attr.'required, onchange=updateCounter(this)');

        $form->addHtml('</div>');
        $form->addHtml('</div>');
    }
    $form->addHtml('</div><br><br>');

} else {
    $form->addHtml('<p style="font-style: italic;">No specimens added to this sample</p>');
}


// jQuery validation
$form->addPlugin('formvalidation', '#add-new-sample', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageRestrictions(true, true, true, true);
$page_render->setPageTitle($_GET['project_id']." > ". $_GET['core_id']. " > ".$_GET['sample_id']." > Sample Count");
$page_render->renderPage();

?>
<style>
    /*The size of the concentration curve*/
    #concentration_curve {
        width:350px !important;
        height:350px !important;
        background-color: white !important;
    }
</style>
<script src="js/Chart.min.js"></script>
<script src="js/driver.js"></script>
<script>
    $( document ).ready(function() {
        $('form').areYouSure(); // Show a popup dialog box when navigating away from sample without saving changes
    });

    // Chart.js load
    (function() {
        loadChartJsPhp();
    })();

    // Undo changes for the sample (only if not previously saved)
    function reload(){
        if (confirm('Are you sure you want to reset the sample?')) {
            $('form').areYouSure( {'silent':true} ); // Disable "are-you-sure" popup
            window.location.reload();
        }
    }

    // Add for charcoal and lycopodium
    function add(element_id) {
        document.getElementById(element_id).value = parseInt(document.getElementById(element_id).value) + 1;
    }

    // Subtract for charcoal and lycopodium
    function subtract(element_id){
        document.getElementById(element_id).value = parseFloat(document.getElementById(element_id).value) - 1;
    }

    // Update counter text on hover
    function updateCounter(element){
        var container = $(element).closest(".specimen-container");
        var counter = container.find(".counter-text");
        var input = container.find("input");
        counter.html(input.val());
        $('form').trigger('rescan.areYouSure'); // Let the "are-you-sure" plugin know that the input was updated
    }


    // Add to specimen counter
    function addToSpecimen(element) {
        var container = $(element).closest(".specimen-container");
        var input = container.find("input");
        var overlay = container.find(".overlay");
        input.val(Number(input.val())+1);
        updateCounter(container);

        $(container).addClass('interaction-highlight');
        $(overlay).addClass('interaction-highlight');
        setTimeout(function () {
            $(container).removeClass('interaction-highlight');
            $(overlay).removeClass('interaction-highlight');
        }, 1000);
    }

    // Store outside function so it doesn't have to be calculated every time on keypress
    var containers = $(".specimen-container");

    // Enable key presses for counter
    window.onkeyup = function(e) {
        <?php
        $keys = json_encode(str_split("qwertyuiopasdfghjklzxcvbnm")); // hotkeys
        ?>
        var key_pressed;
        if (e.key !== undefined) {
            key_pressed = e.key;
        } else if (e.keyIdentifier !== undefined) {
            key_pressed = e.keyIdentifier;
        } else if (e.keyCode !== undefined) {
            key_pressed = e.keyCode;
        }
        let keys = <?=$keys?>;
        containers.each(function(index, e){
            if (key_pressed == keys[index] || key_pressed == keys[index].toUpperCase()) {
                addToSpecimen(this);
                return false;
            }
        });
    };

    // The popover for the graph
    let ref = $("button[name='stats-btn']");
    let popup = $("#chart_div");
    let popper = new Popper(ref, popup, {
        placement: 'bottom'
    });
    ref.click(function() {
        popup.toggle();
        popper.scheduleUpdate();
    });

</script>
