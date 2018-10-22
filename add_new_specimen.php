<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 5/03/2018
 * Time: 3:34 PM
 */

use phpformbuilder\database\Mysql;
use classes\Add_New_Post_Form;

require_once "classes/Page_Renderer.php";
require_once "classes/Add_New_Post_Form.php";

function extract_name($file) {
    $file_array = explode("/", $file);
    return empty($file_array[1]) ? $file_array[0] : $file_array[1];
}

$image_folder = $_SERVER['DOCUMENT_ROOT']."/phpformbuilder/images/uploads/";
if (!file_exists($image_folder)) {
    mkdir($image_folder);
}


class Specimen_Form extends Add_New_Post_Form {

    protected function delete() {
        $this->db->deleteRows($this->table_name, $this->filter);
        if ($this->db->error()) {
            storeDbMsg($this->db);
        } else {
            global $image_folder;
            delete_files($image_folder . $_GET["project_id"] . "/" . $_POST["specimen_id"]. "/");
            $success_message = urlencode(ucwords($this->form_ID) . " successfully deleted!");
            header("location: index.php?success_message=".$success_message);
            exit;
        }
    }

    protected function create() {
        parent::create();
        if (!$this->db->error()) {
            // If specimen added from sample page then also add the specimen to the sample
            if ($_GET['sample_id']) {
                $update_found["specimen_id"] = $this->update["specimen_id"];
                $update_found["sample_id"] = Mysql::SQLValue($_GET['sample_id']);
                $update_found["core_id"] = Mysql::SQLValue($_GET['core_id']);
                $update_found["project_id"] = Mysql::SQLValue($_GET['project_id']);
                $update_found["specimen_project_id"] = Mysql::SQLValue($_GET['project_id']);
                $update_found["count"] = Mysql::SQLValue(1);
                $this->db->insertRow('found_specimens', $update_found);
                storeDbMsg($this->db);

                # Do concentration curve
                $update_curve["sample_id"] = Mysql::SQLValue($_GET['sample_id']);
                $update_curve["core_id"] = Mysql::SQLValue($_GET['core_id']);
                $update_curve["project_id"] = Mysql::SQLValue($_GET['project_id']);
                $this->db->query("SELECT SUM(count) as total FROM found_specimens WHERE sample_id = " . $update_curve["sample_id"] . " AND core_id = " . $update_curve["core_id"] . " AND project_id = " . $update_curve["project_id"]);
                $tally_count = $this->db->recordsArray()[0]["total"];
                $update_curve["tally_count"] = Mysql::SQLValue($tally_count, "int");
                $this->db->query("SELECT COUNT(*) as amount FROM found_specimens WHERE sample_id = " . $update_curve["sample_id"] . " AND core_id = " . $update_curve["core_id"] . " AND project_id = " . $update_curve["project_id"]);
                $unique_spec = $this->db->recordsArray()[0]["amount"];
                $update_curve["unique_spec"] = Mysql::SQLValue($unique_spec, "int");
                $this->db->insertRow('concentration_curve', $update_curve);
                storeDbMsg($this->db,"Specimen successfully added to project and to sample!");
            }
        }
    }

    protected function setUpdateArray() {
        global $image_folder;
        $type = trim($_POST["poll_spore"]);

        //Don't want to change the project_id a specimen is attached to
        //This might change in the future if specimen migration functionality is desired
        $update["project_id"] = Mysql::SQLValue($_GET["project_id"], "text");
        $update["specimen_id"] = Mysql::SQLValue($_POST["specimen_id"], "text");
        $update["family"] = Mysql::SQLValue($_POST["family"], "text");
        $update["genus"] = Mysql::SQLValue($_POST["genus"], "text");
        $update["species"] = Mysql::SQLValue($_POST["species"], "text");
        $update["poll_spore"] = Mysql::SQLValue($type, "text");
        $update["grain_arrangement"] = Mysql::SQLValue($_POST["grain_arrangement"], "text");
        $update["grain_morphology"] = Mysql::SQLValue(implode(",", $_POST["grain_morphology_$type"]), "text"); // poll / spore
        $update["polar_axis_length_min"] = Mysql::SQLValue($_POST["polar_axis_length_min"], "float");
        $update["polar_axis_length_avg"] = Mysql::SQLValue($_POST["polar_axis_length_avg"], "float");
        $update["polar_axis_length_max"] = Mysql::SQLValue($_POST["polar_axis_length_max"], "float");
        $update["polar_axis_n"] = Mysql::SQLValue($_POST["polar_axis_n"], "float");
        $update["equatorial_axis_length_avg"] = Mysql::SQLValue($_POST["equatorial_axis_length_avg"], "float");
        $update["equatorial_axis_n"] = Mysql::SQLValue($_POST["equatorial_axis_n"], "float");
        $update["size"] = Mysql::SQLValue($_POST["size"], "text");
        //$update["***determined***"] = Mysql::SQLValue($_POST["***determined***"], "text");
        //$update["***determined***"] = Mysql::SQLValue($_POST["***determined***"], "text");
        $update["equatorial_shape_minor"] = Mysql::SQLValue($_POST["equatorial_shape_minor"], "text");
        $update["equatorial_shape_major"] = Mysql::SQLValue($_POST["equatorial_shape_major"], "text");
        $update["polar_shape"] = Mysql::SQLValue(implode(",", $_POST["polar_shape"]), "text");
        $update["surface_pattern"] = Mysql::SQLValue($_POST["surface_pattern"], "text");
        $update["wall_thickness"] = Mysql::SQLValue($_POST["wall_thickness"], "float");
        $update["wall_evenness"] = Mysql::SQLValue($_POST["wall_evenness"], "text");
        $update["exine_type"] = Mysql::SQLValue($_POST["exine_type"], "text");
        $update["colporus"] = Mysql::SQLValue($_POST["colporus"], "text");
        $update["L_P"] = Mysql::SQLValue($_POST["L_P"], "float");
        $update["L_E"] = Mysql::SQLValue($_POST["L_E"], "float");
        $update["pore_protrusion"] = Mysql::SQLValue($_POST["pore_protrusion"], "text");
        $update["pore_shape_e"] = Mysql::SQLValue($_POST["pore_shape_e"], "text");
        $update["pore_shape_size"] = Mysql::SQLValue($_POST["pore_shape_size"], "text");
        $update["pore_shape_p"] = Mysql::SQLValue($_POST["pore_shape_p"], "text");
        $update["pore_margin"] = Mysql::SQLValue($_POST["pore_margin"], "text");
        $update["colpus_sulcus_length_c"] = Mysql::SQLValue($_POST["colpus_sulcus_length_c_$type"], "float"); // poll / spore
        $update["colpus_sulcus_shape"] = Mysql::SQLValue($_POST["colpus_sulcus_shape_$type"], "text"); // poll / spore
        //$update["***determined***"] = Mysql::SQLValue($_POST["***determined***"], "text");
        $update["colpus_sulcus_margin"] = Mysql::SQLValue($_POST["colpus_sulcus_margin_$type"], "text"); // poll / spore
        $update["apocolpium_width_e"] = Mysql::SQLValue($_POST["apocolpium_width_e"], "float");
        //$update["***determined***"] = Mysql::SQLValue($_POST["***determined***"], "text");
        $update["trilete_scar_arm_length"] = Mysql::SQLValue($_POST["trilete_scar_arm_length"], "text");
        $update["trilete_scar_shape"] = Mysql::SQLValue($_POST["trilete_scar_shape"], "text");
        $update["p_sacci_size"] = Mysql::SQLValue($_POST["p_sacci_size"], "text");
        $update["e_sacci_size"] = Mysql::SQLValue($_POST["e_sacci_size"], "text");
        $update["plant_function_type"] = Mysql::SQLValue(implode(",", $_POST["plant_function_type"]), "text");
        $update["morphology_notes"] = Mysql::SQLValue($_POST["morphology_notes"], "text");

        if (!empty($_POST["uploaded-images"])) {
            $update["image_folder"] = Mysql::SQLValue($image_folder . $_GET["project_id"] . "/" . $_POST["specimen_id"]. "/", "text");
            mkdir($image_folder . $_GET["project_id"] . "/" . $_POST["specimen_id"]);
            $uploaded_images = json_decode($_POST["uploaded-images"], true);
            $update["primary_image"] = Mysql::SQLValue(extract_name($uploaded_images[0]["file"]), "text");
            foreach ($uploaded_images as $image) {
                $image = extract_name($image["file"]);
                rename($image_folder . $image, $image_folder . $_GET["project_id"] . "/" . $_POST["specimen_id"]. "/" . $image);
            }
        }

        $this->update = $update;
    }

    protected function fillFormWithDbValues($record_array) {
        $specimen = $record_array;
        $_SESSION[$this->form_ID]["specimen_id"] = $specimen["specimen_id"];
        $_SESSION[$this->form_ID]["family"] = $specimen["family"];
        $_SESSION[$this->form_ID]["genus"] = $specimen["genus"];
        $_SESSION[$this->form_ID]["species"] = $specimen["species"];
        $_SESSION[$this->form_ID]["poll_spore"] = $specimen["poll_spore"];
        $_SESSION[$this->form_ID]["grain_arrangement"] = $specimen["grain_arrangement"];
        $_SESSION[$this->form_ID]["grain_morphology_" . $specimen["poll_spore"]] = explode(",", $specimen["grain_morphology"]); // poll / spore
        $_SESSION[$this->form_ID]["polar_axis_length_min"] = $specimen["polar_axis_length_min"];
        $_SESSION[$this->form_ID]["polar_axis_length_avg"] = $specimen["polar_axis_length_avg"];
        $_SESSION[$this->form_ID]["polar_axis_length_max"] = $specimen["polar_axis_length_max"];
        $_SESSION[$this->form_ID]["polar_axis_n"] = $specimen["polar_axis_n"];
        $_SESSION[$this->form_ID]["equatorial_axis_length_min"] = $specimen["equatorial_axis_length_min"];
        $_SESSION[$this->form_ID]["equatorial_axis_length_avg"] = $specimen["equatorial_axis_length_avg"];
        $_SESSION[$this->form_ID]["equatorial_axis_length_max"] = $specimen["equatorial_axis_length_max"];
        $_SESSION[$this->form_ID]["equatorial_axis_n"] = $specimen["equatorial_axis_n"];
        $_SESSION[$this->form_ID]["size"] = $specimen["size"];
        //$_SESSION[$this->form_ID]["***determined***"] = $specimen["***determined***"];
        //$_SESSION[$this->form_ID]["***determined***"] = $specimen["***determined***"];
        $_SESSION[$this->form_ID]["equatorial_shape_minor"] = $specimen["equatorial_shape_minor"];
        $_SESSION[$this->form_ID]["equatorial_shape_major"] = $specimen["equatorial_shape_major"];
        $_SESSION[$this->form_ID]["polar_shape"] = explode(",", $specimen["polar_shape"]);
        $_SESSION[$this->form_ID]["surface_pattern"] = $specimen["surface_pattern"];
        $_SESSION[$this->form_ID]["wall_thickness"] = $specimen["wall_thickness"];
        $_SESSION[$this->form_ID]["wall_evenness"] = $specimen["wall_evenness"];
        $_SESSION[$this->form_ID]["exine_type"] = $specimen["exine_type"];
        $_SESSION[$this->form_ID]["colporus"] = $specimen["colporus"];
        $_SESSION[$this->form_ID]["L_P"] = $specimen["L_P"];
        $_SESSION[$this->form_ID]["L_E"] = $specimen["L_E"];
        $_SESSION[$this->form_ID]["pore_protrusion"] = $specimen["pore_protrusion"];
        $_SESSION[$this->form_ID]["pore_shape_e"] = $specimen["pore_shape_e"];
        $_SESSION[$this->form_ID]["pore_shape_size"] = $specimen["pore_shape_size"];
        $_SESSION[$this->form_ID]["pore_shape_p"] = $specimen["pore_shape_p"];
        $_SESSION[$this->form_ID]["pore_margin"] = $specimen["pore_margin"];
        $_SESSION[$this->form_ID]["colpus_sulcus_length_c_" . $specimen["poll_spore"]] = $specimen["colpus_sulcus_length_c"]; // poll / spore
        $_SESSION[$this->form_ID]["colpus_sulcus_shape_" . $specimen["poll_spore"]] = $specimen["colpus_sulcus_shape"]; // poll / spore
        //$_SESSION[$this->form_ID]["***determined***"] = $specimen["***determined***"];
        $_SESSION[$this->form_ID]["colpus_sulcus_margin_" . $specimen["poll_spore"]] = $specimen["colpus_sulcus_margin"]; // poll / spore
        $_SESSION[$this->form_ID]["apocolpium_width_e"] = $specimen["apocolpium_width_e"];
        //$_SESSION[$this->form_ID]["***determined***"] = $specimen["***determined***"];
        $_SESSION[$this->form_ID]["trilete_scar_arm_length"] = $specimen["trilete_scar_arm_length"];
        $_SESSION[$this->form_ID]["trilete_scar_shape"] = $specimen["trilete_scar_shape"];
        $_SESSION[$this->form_ID]["p_sacci_size"] = $specimen["p_sacci_size"];
        $_SESSION[$this->form_ID]["e_sacci_size"] = $specimen["e_sacci_size"];
        $_SESSION[$this->form_ID]["plant_function_type"] = explode(",", $specimen["plant_function_type"]);
        $_SESSION[$this->form_ID]["morphology_notes"] = $specimen["morphology_notes"];
    }
}

/* ==================================================
    The Form
================================================== */

$form = new Specimen_Form("specimen", "specimens", 'horizontal', 'novalidate', 'bs4');

if ($_GET['sample_id']) {
    $form->addHtml("<p style='font-style: italic'>Note: This will add the specimen to the website's database and also add it to the current sample: ".$_GET['sample_id']."</p>");
}

#######################
# Universal Fields
#######################

# Code ID and Family
$form->startFieldset('General');
$form->setCols(4, 4);
$form->groupInputs('specimen_id', 'family');
$form->addHelper('Specimen ID<sup class="text-danger">* </sup>', 'specimen_id');
$form->setOptions(array('requiredMark'=>''));
if ($_GET["edit"]) {
    $form->addInput('text', 'specimen_id', '', 'Descriptors', 'required, readonly=readonly');
} else {
    $form->addInput('text', 'specimen_id', '', 'Descriptors', 'required'); // Need to have warning for code !!!!!!!!!
}
$form->setOptions(array('requiredMark'=>'<sup class="text-danger">* </sup>'));
$form->setCols(0, 4);
$form->addHelper('Family', 'family');
$form->addInput('text', 'family', '', '', '');
$form->setCols(4, 8);

# Genus and Species and Family
$form->setCols(4, 4);
$form->groupInputs('genus', 'species');
$form->addHelper('Genus', 'genus');
$form->addInput('text', 'genus', '', '', '');
$form->setCols(0, 4);
$form->addHelper('Species', 'species');
$form->addInput('text', 'species', '', '', '');

# Type
$form->setCols(4, 8);
$form->addHelper('Pollen/Spore', 'poll_spore');
$form->addOption('poll_spore', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('poll_spore', 'pollen', 'Pollen', '', '');
$form->addOption('poll_spore', 'spore', 'Spore', '', '');
$form->addSelect('poll_spore', 'Type', 'class=select2, data-width=100%, required');

$form->endFieldset();
$form->startFieldset('Morphology');

# 2. Grain Arrangement
$form->setCols(4, 8);
$form->addOption('grain_arrangement', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('grain_arrangement', 'monad', 'Monad', '', '');
$form->addOption('grain_arrangement', 'dyad', 'Dyad', '', '');
$form->addOption('grain_arrangement', 'tetrad', 'Tetrad', '', '');
$form->addOption('grain_arrangement', 'polyad', 'Polyad', '', '');
$form->addSelect('grain_arrangement', 'Grain Arrangement', 'class=select2, data-width=100%');


$form->startDependentFields('poll_spore', 'spore'); # Spore Fields

# 3. Grain Morphology  [spore]
$form->addOption('grain_morphology_spore', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('grain_morphology_spore', 'monolete', 'Monolete', '', '');
$form->addOption('grain_morphology_spore', 'trilete', 'Trilete', '', '');
$form->addSelect('grain_morphology_spore', 'Grain Morphology ', 'class=select2, data-width=100%');

$form->endDependentFields(); # End Spore Fields

$form->startDependentFields('poll_spore', 'pollen'); # Pollen Fields

# 3. Grain Morphology [pollen]
$form->addOption('grain_morphology_pollen[]', 'inaperturate',  'inaperturate', '', '');
$form->addOption('grain_morphology_pollen[]', 'monoporate',  'monoporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'monocolpate / monosulcate',  'monocolpate / monosulcate', '', '');
$form->addOption('grain_morphology_pollen[]', 'diporate',  'diporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'dicolpate',  'dicolpate', '', '');
$form->addOption('grain_morphology_pollen[]', 'dicolporate',  'dicolporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'triporate',  'triporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'tricolpate',  'tricolpate', '', '');
$form->addOption('grain_morphology_pollen[]', 'tricolporate',  'tricolporate', '', '');
$form->addOption('grain_morphology_pollen[]', '4 porate',  '4 porate', '', '');
$form->addOption('grain_morphology_pollen[]', '4 colpate',  '4 colpate', '', '');
$form->addOption('grain_morphology_pollen[]', '4 colporate',  '4 colporate', '', '');
$form->addOption('grain_morphology_pollen[]', '5 porate',  '5 porate', '', '');
$form->addOption('grain_morphology_pollen[]', '5 colpate',  '5 colpate', '', '');
$form->addOption('grain_morphology_pollen[]', '5 colporate',  '5 colporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'zonoporate',  'zonoporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'zonocolpate',  'zonocolpate', '', '');
$form->addOption('grain_morphology_pollen[]', 'zonocolporate',  'zonocolporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'pantoporate',  'pantoporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'pantocolpate',  'pantocolpate', '', '');
$form->addOption('grain_morphology_pollen[]', 'pantocolporate',  'pantocolporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'heterocolpate',  'heterocolpate', '', '');
$form->addOption('grain_morphology_pollen[]', 'fenestrate',  'fenestrate', '', '');
$form->addOption('grain_morphology_pollen[]', 'syncopate / syncolporate',  'syncopate / syncolporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'parasyncolpate / parasyncolporate',  'parasyncolpate / parasyncolporate', '', '');
$form->addOption('grain_morphology_pollen[]', 'vesiculate / saccate',  'vesiculate / saccate', '', '');
$form->addOption('grain_morphology_pollen[]', 'spiraperturate',  'spiraperturate', '', '');
$form->addHelper('Multiple Choice', 'grain_morphology_pollen[]');
$form->addSelect('grain_morphology_pollen[]', 'Grain Morphology ', 'class=select2, data-width=100%, multiple=multiple');
// https://www.phpformbuilder.pro/templates/bootstrap-4-forms/email-styles.php !!!!!!!!!!!


# !!!!!!!!!!!!! NEED TO ADD IMAGES
/*$form->addHelper('Grain Morphology', 'landscapes');
for ($i=0; $i < 10; $i++) {
    $form->addOption('grain_morphology[]', 'https://www.phpformbuilder.pro/templates/assets/img/random_images/landscapes/landscape_' . $i . '.jpg', '', '', 'data_img_src=https://www.phpformbuilder.pro/templates/assets/img/random_images/landscapes/landscape_' . $i . '.jpg, data_img_alt=Landscape ' . $i);
}
$form->addSelect('grain_morphology[]', 'Choose Grain Morphology', 'class=show_label, multiple');*/

$form->endDependentFields(); # End Pollen Fields

$form->endFieldset();

#######################
# Measurements
#######################
$form->startFieldset('Shape & Size');
$form->setOptions(array('buttonWrapper'=>''));

# Polar axis length
$form->setCols(4, 2);
$form->groupInputs('polar_axis_length_min', 'polar_axis_length_avg', 'polar_axis_length_max', 'polar_axis_n');
$form->addHelper('Min (rounded to 1dp on save)', 'polar_axis_length_min');
$form->addHelper('Avg. (rounded to 1dp on save)', 'polar_axis_length_avg');
$form->addHelper('Max (rounded to 1dp on save)', 'polar_axis_length_max');
$form->addHelper('Number of measurements', 'polar_axis_n');
if (!$_GET["edit"]) {
    unset($_SESSION[$form->getFormName()]['polar_axis_length_min']);
    unset($_SESSION[$form->getFormName()]['polar_axis_length_avg']);
    unset($_SESSION[$form->getFormName()]['polar_axis_length_max']);
    unset($_SESSION[$form->getFormName()]['polar_axis_n']);
}
$form->addInput('number', 'polar_axis_length_min', '', 'Polar axis length (µm)', 'readonly="readonly"');
$form->addInput('number', 'polar_axis_length_avg', '', '', 'readonly="readonly"');
$form->addInput('number', 'polar_axis_length_max', '', '', 'readonly="readonly"');
$form->addInput('number', 'polar_axis_n', '', '', 'readonly="readonly"');

$form->setCols(4, 2);
unset($_SESSION[$form->getFormName()]['polar_axis_input']);
$form->groupInputs('polar_axis_input', 'polar_average');
$form->addHelper('New measurement', 'polar_axis_input');
$form->addInput('number', 'polar_axis_input', '', '', '');
$form->setCols(4, 6);
$form->addBtn('button', 'polar_axis_merge_button', "merge", '<i class="fa fa-plus" aria-hidden="true"></i> Add data point', 'class=btn btn-success, data-style=zoom-in, onclick=add_data_point(\'polar\')', 'polar');
$form->addBtn('button', 'polar_axis_reset_button', "reset", '<i class="fa fa-trash" aria-hidden="true"></i> Delete all data points', 'class=btn btn-danger, data-style=zoom-in, onclick=if(confirm(\'Are you sure you want to delete all polar data points?\')) reset_data(\'polar\')', 'polar');
$form->printBtnGroup('polar');
$form->addHtml('</div>');

# Equatorial axis length
$form->setCols(4, 2);
$form->groupInputs('equatorial_axis_length_min', 'equatorial_axis_length_avg', 'equatorial_axis_length_max', 'equatorial_axis_n');
$form->addHelper('Min (rounded to 1dp on save)', 'equatorial_axis_length_min');
$form->addHelper('Avg. (rounded to 1dp on save)', 'equatorial_axis_length_avg');
$form->addHelper('Max (rounded to 1dp on save)', 'equatorial_axis_length_max');
$form->addHelper('Number of measurements', 'equatorial_axis_n');
if (!$_GET["edit"]) {
    unset($_SESSION[$form->getFormName()]['equatorial_axis_length_min']);
    unset($_SESSION[$form->getFormName()]['equatorial_axis_length_avg']);
    unset($_SESSION[$form->getFormName()]['equatorial_axis_length_max']);
    unset($_SESSION[$form->getFormName()]['equatorial_axis_n']);
}
$form->addInput('number', 'equatorial_axis_length_min', '', 'Equatorial axis length (µm)', 'readonly="readonly"');
$form->addInput('number', 'equatorial_axis_length_avg', '', '', 'readonly="readonly"');
$form->addInput('number', 'equatorial_axis_length_max', '', '', 'readonly="readonly"');
$form->addInput('number', 'equatorial_axis_n', '', '', 'readonly="readonly"');

$form->setCols(4, 2);
unset($_SESSION[$form->getFormName()]['equatorial_axis_input']);
$form->groupInputs('equatorial_axis_input', 'equatorial_average');
$form->addHelper('New measurement', 'equatorial_axis_input');
$form->addInput('number', 'equatorial_axis_input', '', '', '');
$form->setCols(4, 6);
$form->addBtn('button', 'equatorial_axis_merge_button', "merge", '<i class="fa fa-plus" aria-hidden="true"></i> Add data point', 'class=btn btn-success, data-style=zoom-in, onclick=add_data_point(\'equatorial\')', 'equatorial');
$form->addBtn('button', 'equatorial_axis_reset_button', "reset", '<i class="fa fa-trash" aria-hidden="true"></i> Delete all data points', 'class=btn btn-danger, data-style=zoom-in, onclick=if(confirm(\'Are you sure you want to delete all equatorial data points?\')) reset_data(\'equatorial\')', 'equatorial');
$form->printBtnGroup('equatorial');
$form->addHtml('</div>');

$form->setOptions(array('buttonWrapper'=>'<div class="form-group row justify-content-end"></div>'));

# 6. Size
$form->setCols(4, 8);
$form->addOption('size', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('size', 'very small', 'Very Small;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp max[P,E]<10');
$form->addOption('size', 'small',      'Small;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp 10<=max[P,E]<25');
$form->addOption('size', 'medium',     'Medium;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp 25<=max[P,E]<50');
$form->addOption('size', 'large',      'Large;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp 50<=max[P,E]<100');
$form->addOption('size', 'very large', 'Very Large;&nbsp&nbsp&nbsp 100<=max[P,E]<200');
$form->addOption('size', 'gigantic', 'Gigantic;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp max[P,E]>=200');
$form->addSelect('size','Size','class=select2, data-width=100%');

# 7. Equatorial shape (major)
$form->setCols(4, 8);
$form->addOption('equatorial_shape_major', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('equatorial_shape_major', 'peroblate',         'peroblate;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspP/E<0.5');
$form->addOption('equatorial_shape_major', 'oblate',            'oblate;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp0.5<=P/E<0.76');
$form->addOption('equatorial_shape_major', 'suboblate',         'suboblate;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp0.76<=P/E<0.89');
$form->addOption('equatorial_shape_major', 'oblate-spheroidal',  'oblate-spheroidal;&nbsp&nbsp&nbsp0.89<=P/E<0.99');
$form->addOption('equatorial_shape_major', 'spheroidal',        'spheroidal;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp0.99<=P/E<1.01');
$form->addOption('equatorial_shape_major', 'prolate-spheroidal', 'prolate-spheroidal;&nbsp1.01&lt=P/E<1.15');
$form->addOption('equatorial_shape_major', 'subprolate',        'subprolate;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp1.15<=P/E<1.34');
$form->addOption('equatorial_shape_major', 'prolate',           'prolate;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp1.34<=P/E<2.00');
$form->addOption('equatorial_shape_major', 'perprolate',        'perprolate;&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspP/E>=2.00');
$form->addSelect('equatorial_shape_major', 'Equatorial shape (major)', 'class=select2, data-width=100%');

# 8. Equatorial shape (minor)
$form->setCols(4, 8);
$form->addOption('equatorial_shape_minor', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('equatorial_shape_minor', 'rounded ', 'rounded ', '', '');
$form->addOption('equatorial_shape_minor', 'rectangular', 'rectangular', '', '');
$form->addOption('equatorial_shape_minor', 'rhombic', 'rhombic', '', '');
$form->addOption('equatorial_shape_minor', 'triangular', 'triangular', '', '');
$form->addOption('equatorial_shape_minor', 'bilateral', 'bilateral', '', '');
$form->addSelect('equatorial_shape_minor', 'Equatorial shape (minor)', 'class=select2, data-width=100%');

# 9. *Polar shape (following Huang 1972)
$form->addOption('polar_shape[]', 'circular',  'circular', '', '');
$form->addOption('polar_shape[]', 'circular-lobate',  'circular-lobate', '', '');
$form->addOption('polar_shape[]', 'semiangular',  'semiangular', '', '');
$form->addOption('polar_shape[]', 'inter-semi-angular',  'inter-semi-angular', '', '');
$form->addOption('polar_shape[]', 'angular',  'angular', '', '');
$form->addOption('polar_shape[]', 'inter-angular',  'inter-angular', '', '');
$form->addOption('polar_shape[]', 'semi-lobate',  'semi-lobate', '', '');
$form->addOption('polar_shape[]', 'inter-semi-lobate',  'inter-semi-lobate', '', '');
$form->addOption('polar_shape[]', 'lobate',  'lobate', '', '');
$form->addOption('polar_shape[]', 'inter-lobate',  'inter-lobate', '', '');
$form->addOption('polar_shape[]', 'hexagonal',  'hexagonal', '', '');
$form->addOption('polar_shape[]', 'subangular',  'subangular', '', '');
$form->addOption('polar_shape[]', 'inter-subangular',  'inter-subangular', '', '');
$form->addOption('polar_shape[]', 'rectangular (rhomboidal)',  'rectangular (rhomboidal)', '', '');
$form->addOption('polar_shape[]', 'tubular',  'tubular', '', '');
$form->addHelper('Multiple Choice', 'polar_shape[]');
$form->addSelect('polar_shape[]', 'Polar shape ', 'class=select2, data-width=100%, multiple=multiple');

/*$form->startFieldset('Single image with labels');
for ($i=0; $i < 10; $i++) {
    $form->addOption('polar_shape[]', 'https://www.phpformbuilder.pro/templates/assets/img/random-images/sports/sport-' . $i . '.jpg', '', '', 'data-img-src=https://www.phpformbuilder.pro/templates/assets/img/random-images/sports/sport-' . $i . '.jpg, data-img-label=Sport ' . $i . ', data-img-alt=Sport' . $i);
}
$form->addSelect('polar_shape[]', 'Choose your favourite sport', 'multiple, class=show_label, required');
$form->endFieldset();*/

# 10. *Surface pattern
$form->setCols(4, 8);
$form->addOption('surface_pattern', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('surface_pattern', 'psilate',  'psilate', '', '');
$form->addOption('surface_pattern', 'granulate',  'granulate', '', '');
$form->addOption('surface_pattern', 'reticulate',  'reticulate', '', '');
$form->addOption('surface_pattern', 'baculate',  'baculate', '', '');
$form->addOption('surface_pattern', 'clavate',  'clavate', '', '');
$form->addOption('surface_pattern', 'gemmate',  'gemmate', '', '');
$form->addOption('surface_pattern', 'echinate',  'echinate', '', '');
$form->addOption('surface_pattern', 'fossulate',  'fossulate', '', '');
$form->addOption('surface_pattern', 'foveolate',  'foveolate', '', '');
$form->addOption('surface_pattern', 'perforate',  'perforate', '', '');
$form->addOption('surface_pattern', 'rugulate',  'rugulate', '', '');
$form->addOption('surface_pattern', 'striate',  'striate', '', '');
$form->addSelect('surface_pattern', 'Surface pattern ', 'class=select2, data-width=100%');

$form->endFieldset();

#######################
# Wall
#######################
$form->startFieldset('Wall');

# 11. Wall thickness
$form->addHelper('Measurement in (µm)', 'wall_thickness');
$form->addInput('number', 'wall_thickness', '', 'Wall thickness ', '');

# 12. Wall evenness
$form->addOption('wall_evenness', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('wall_evenness', 'even', 'even', '', '');
$form->addOption('wall_evenness', 'thicker on pole', 'thicker on pole', '', '');
$form->addOption('wall_evenness', 'thinner on pole', 'thinner on pole', '', '');
$form->addSelect('wall_evenness', 'Wall evenness ', 'class=select2, data-width=100%');

# 13. Exine type
$form->addOption('exine_type', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('exine_type', 'intectate', 'intectate', '', '');
$form->addOption('exine_type', 'tectate', 'tectate', '', '');
$form->addOption('exine_type', 'semitectate', 'semitectate', '', '');
$form->addSelect('exine_type', 'Exine type ', 'class=select2, data-width=100%');

$form->endFieldset();

#######################
# Apertures
#######################
//$form->startDependentFields('grain_morphology', 'inaperturate,vesiculate / saccate,fenestrate' , true); # (Apertures) irrelevant if 3.b = only 3.b.i or 3.b.xxvi or 3.b.xxiii
$form->startFieldset('Apertures');

# 14. Colporus
$form->startDependentFields('grain_morphology_pollen[]', 'dicolporate,tricolporate,4 colporate,5 colporate,zonocolporate,pantocolporate,syncopate / syncolporate,parasyncolpate / parasyncolporate' ); # if 3.b = any of the following 3.b.vi 3.b.ix 3.b.xii 3.b.xv 3.b.xviii 3.b.xxi 3.b.xxiv 3.b.xxv
$form->addOption('colporus', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('colporus', 'lolongate', 'lolongate', '', '');
$form->addOption('colporus', 'lalongate', 'lalongate', '', '');
$form->addSelect('colporus', 'Colporus ', 'class=select2, data-width=100%');
$form->endDependentFields();

# 15. - 19 (this field should appear if 3.b = any of the following 3.b.ii 3.b.iv 3.b.vi 3.b.vii 3.b.ix 3.b.x 3.b.xii 3.b.xiii 3.b.xv 3.b.xvi 3.b.xix 3.b.xxi 3.b.xxiv 3.b.xxv )
$form->startDependentFields('grain_morphology_pollen[]', 'monoporate,diporate,dicolporate,triporate,tricolporate,4 porate,4 colporate,5 porate,5 colporate,zonoporate,pantoporate,pantocolporate,syncopate / syncolporate,parasyncolpate / parasyncolporate' ); # if 3.b = any of the following 3.b.vi 3.b.ix 3.b.xii 3.b.xv 3.b.xviii 3.b.xxi 3.b.xxiv 3.b.xxv

# 15. Pore size
$form->addHelper('Measurement in (µm)', 'L_P');
$form->addInput('number', 'L_P', '', 'Length parallel to P ', '');
$form->addHelper('Measurement in (µm)', 'L_E');
$form->addInput('number', 'L_E', '', 'Length parallel to E ', '');

# 16. Pore protrusion
$form->addOption('pore_protrusion', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('pore_protrusion', 'through exine (entire wall)', 'through exine (entire wall)', '', '');
$form->addOption('pore_protrusion', 'through sexine (ectoaperture)', 'through sexine (ectoaperture)', '', '');
$form->addOption('pore_protrusion', 'through nexine (endoaperture)', 'through nexine (endoaperture)', '', '');
$form->addOption('pore_protrusion', 'intermediate (mesoaperturate)', 'intermediate (mesoaperturate)', '', '');
$form->addSelect('pore_protrusion', 'Pore protrusion ', 'class=select2, data-width=100%');

# 17. Pore shape
$form->addOption('pore_shape_e', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('pore_shape_e', 'circular', 'circular', '', '');
$form->addOption('pore_shape_e', 'elliptical', 'elliptical', '', '');
$form->addOption('pore_shape_e', 'rectangular', 'rectangular', '', '');
$form->addOption('pore_shape_e', 'rhombic', 'rhombic', '', '');
$form->addSelect('pore_shape_e', 'Pore Shape - E View ', 'class=select2, data-width=100%');

# 17.a (May be worth being in other column)
$form->startDependentFields('pore_shape_e', 'rectangular,rhombic'); # if not elliptical
$form->addOption('pore_shape_size', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('pore_shape_size', 'tall', 'tall', '', '');
$form->addOption('pore_shape_size', 'broad', 'broad', '', '');
$form->addOption('pore_shape_size', 'square', 'square', '', '');
$form->addSelect('pore_shape_size', 'Pore Shape Size ', 'class=select2, data-width=100%');
$form->endDependentFields();

$form->startDependentFields('pore_shape_e', 'elliptical'); # if not elliptical
$form->addOption('pore_shape_size_elps', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('pore_shape_size_elps', 'tall', 'tall', '', '');
$form->addOption('pore_shape_size_elps', 'broad', 'broad', '', '');
$form->addSelect('pore_shape_size_elps', 'Pore Shape Size ', 'class=select2, data-width=100%');
$form->endDependentFields();


# 18. Pore shape – P_view
$form->addOption('pore_shape_p', '',  'Choose one ...', '', 'disabled, selected');
$form->addOption('pore_shape_p', 'globe (thickened)',  'globe (thickened)', '', '');
$form->addOption('pore_shape_p', 'drop',  'drop', '', '');
$form->addOption('pore_shape_p', 'club',  'club', '', '');
$form->addOption('pore_shape_p', 'vestibulum',  'vestibulum', '', '');
$form->addOption('pore_shape_p', 'atrium',  'atrium', '', '');
$form->addOption('pore_shape_p', 'labrum',  'labrum', '', '');
$form->addOption('pore_shape_p', 'common',  'common', '', '');
$form->addOption('pore_shape_p', 'ragged',  'ragged', '', '');
$form->addOption('pore_shape_p', 'thinned',  'thinned', '', '');
$form->addSelect('pore_shape_p', 'Pore Shape - P View ', 'class=select2, data-width=100%');

# 19. Pore margin
$form->addOption('pore_margin', '',  'Choose one ...', '', 'disabled, selected');
$form->addOption('pore_margin', 'with annulus',  'with annulus', '', '');
$form->addOption('pore_margin', 'without annulus',  'without annulus', '', '');
$form->addOption('pore_margin', 'ragged',  'ragged', '', '');
$form->addSelect('pore_margin', 'Pore Margin ', 'class=select2, data-width=100%');

$form->endDependentFields(); # for 15 - 19

# 20. Colpus / Sulcus length (C) [spore & pollen]
$form->startDependentFields('grain_morphology_spore', 'Monolete,Trilete' ); # if 3.a = 3.a.i 3.a.ii
$form->addHelper('Measurement in (µm)', 'colpus_sulcus_length_c_spore');
$form->addInput('number', 'colpus_sulcus_length_c_spore', '', 'Colpus / Sulcus length (C) ', '');
$form->endDependentFields();

$form->startDependentFields('grain_morphology_pollen[]', 'monocolpate / monosulcate,dicolpate,dicolporate,tricolpate,tricolporate,4 colpate,4 colporate,5 colpate,5 colporate,zonocolpate,zonocolporate,pantocolpate,pantocolporate,heterocolpate,syncopate / syncolporate,parasyncolpate / parasyncolporate,spiraperturate' ); # if 3.b = any of the following 3.b.iii 3.b.v 3.b.vi 3.b.viii 3.b.ix 3.b.xi 3.b.xii 3.b.xiv 3.b.xv 3.b.xvii 3.b.xviii 3.b.xx 3.b.xxi 3.b.xxii 3.b.xxiv 3.b.xxv 3.b.xxvii
$form->addHelper('Measurement in (µm)', 'colpus_sulcus_length_c_pollen');
$form->addInput('number', 'colpus_sulcus_length_c_pollen', '', 'Colpus / Sulcus length (C) ', '');
$form->endDependentFields();

# 21. Colpus / Sulcus shape [spore & pollen]
$form->startDependentFields('grain_morphology_spore', 'Monolete' ); # if 3.a = 3.a.i 3.a.ii
$form->addOption('colpus_sulcus_shape_spore', '',  'Choose one ...', '', 'disabled, selected');
$form->addOption('colpus_sulcus_shape_spore', 'splayed',  'splayed', '', '');
$form->addOption('colpus_sulcus_shape_spore', 'open at equator',  'open at equator', '', '');
$form->addOption('colpus_sulcus_shape_spore', 'open at pole',  'open at pole', '', '');
$form->addOption('colpus_sulcus_shape_spore', 'pinches at equator',  'pinches at equator', '', '');
$form->addOption('colpus_sulcus_shape_spore', 'straight',  'straight', '', '');
$form->addSelect('colpus_sulcus_shape_spore', 'Colpus / Sulcus shape ', 'class=select2, data-width=100%');
$form->endDependentFields();

$form->startDependentFields('grain_morphology_pollen[]', 'monocolpate / monosulcate,dicolpate,dicolporate,tricolpate,tricolporate,4 colpate,4 colporate,5 colpate,5 colporate,zonocolpate,zonocolporate,pantocolpate,pantocolporate,heterocolpate,syncopate / syncolporate,parasyncolpate / parasyncolporate,spiraperturate' ); # if 3.b = any of the following 3.b.iii 3.b.v 3.b.vi 3.b.viii 3.b.ix 3.b.xi 3.b.xii 3.b.xiv 3.b.xv 3.b.xvii 3.b.xviii 3.b.xx 3.b.xxi 3.b.xxii 3.b.xxiv 3.b.xxv 3.b.xxvii
$form->addOption('colpus_sulcus_shape_pollen', '',  'Choose one ...', '', 'disabled, selected');
$form->addOption('colpus_sulcus_shape_pollen', 'splayed',  'splayed', '', '');
$form->addOption('colpus_sulcus_shape_pollen', 'open at equator',  'open at equator', '', '');
$form->addOption('colpus_sulcus_shape_pollen', 'open at pole',  'open at pole', '', '');
$form->addOption('colpus_sulcus_shape_pollen', 'pinches at equator',  'pinches at equator', '', '');
$form->addOption('colpus_sulcus_shape_pollen', 'straight',  'straight', '', '');
$form->addSelect('colpus_sulcus_shape_pollen', 'Colpus / Sulcus shape ', 'class=select2, data-width=100%');
$form->endDependentFields();

# 22. C/P (autogenerated)

# 23. Colpus / Sulcus margin [spore & pollen]
$form->startDependentFields('grain_morphology_spore', 'Monolete,Trilete ' ); # if 3.a = 3.a.i 3.a.ii
$form->addOption('colpus_sulcus_margin_spore', '',  'Choose one ...', '', 'disabled, selected');
$form->addOption('colpus_sulcus_margin_spore', 'with margo',  'with margo', '', '');
$form->addOption('colpus_sulcus_margin_spore', 'without margo',  'without margo', '', '');
$form->addSelect('colpus_sulcus_margin_spore', 'Colpus / Sulcus margin ', 'class=select2, data-width=100%');
$form->endDependentFields();

$form->startDependentFields('grain_morphology_pollen[]', 'monocolpate / monosulcate,dicolpate,dicolporate,tricolpate,tricolporate,4 colpate,4 colporate,5 colpate,5 colporate,zonocolpate,zonocolporate,pantocolpate,pantocolporate,heterocolpate,syncopate / syncolporate,parasyncolpate / parasyncolporate,spiraperturate' ); # if 3.b = any of the following 3.b.iii 3.b.v 3.b.vi 3.b.viii 3.b.ix 3.b.xi 3.b.xii 3.b.xiv 3.b.xv 3.b.xvii 3.b.xviii 3.b.xx 3.b.xxi 3.b.xxii 3.b.xxiv 3.b.xxv 3.b.xxvii
$form->addOption('colpus_sulcus_margin_pollen', '',  'Choose one ...', '', 'disabled, selected');
$form->addOption('colpus_sulcus_margin_pollen', 'with margo',  'with margo', '', '');
$form->addOption('colpus_sulcus_margin_pollen', 'without margo',  'without margo', '', '');
$form->addSelect('colpus_sulcus_margin_pollen', 'Colpus / Sulcus margin ', 'class=select2, data-width=100%');
$form->endDependentFields();

# 24. Apocolpium field
$form->startDependentFields('grain_morphology_pollen[]', 'monocolpate / monosulcate,dicolpate,dicolporate,tricolpate,tricolporate,4 colpate,4 colporate,5 colpate,5 colporate,zonocolpate,zonocolporate,pantocolpate,pantocolporate,heterocolpate,syncopate / syncolporate,parasyncolpate / parasyncolporate,spiraperturate' ); # if 3.b = any of the following 3.b.iii 3.b.v 3.b.vi 3.b.viii 3.b.ix 3.b.xi 3.b.xii 3.b.xiv 3.b.xv 3.b.xvii 3.b.xviii 3.b.xx 3.b.xxi 3.b.xxii 3.b.xxiv 3.b.xxv 3.b.xxvii
$form->addHelper('Measurement in (µm)', 'apocolpium_width_e');
$form->addInput('number', 'apocolpium_width_e', '', 'Apocolpium Width (e) ', '');
# 24.b is autogenerated
$form->endDependentFields();

# 25. Trilete scar arm length
$form->startDependentFields('grain_morphology_spore', 'Trilete' );
$form->addHelper('Measurement in (µm)', 'trilete_scar_arm_length');
$form->addInput('number', 'trilete_scar_arm_length', '', 'Trilete scar arm length ', '');

# 26. Trilete scar shape
$form->addCheckbox('trilete_scar_shape', 'open',  'open');
$form->addCheckbox('trilete_scar_shape', 'closed',  'closed');
$form->addCheckbox('trilete_scar_shape', 'straight',  'straight');
$form->addCheckbox('trilete_scar_shape', 'wavy',  'wavy');
$form->printCheckboxGroup('trilete_scar_shape', 'Trilete scar shape ', false, '');
$form->endDependentFields();

# 27. P x E sacci size
$form->startDependentFields('grain_morphology_pollen[]', 'vesiculate / saccate' ); # if 3.b = 3.b.xxvi
$form->addHelper('Measurement in (µm)', 'p_sacci_size');
$form->addInput('number', 'p_sacci_size', '', 'P sacci size ', '');
$form->addHelper('Measurement in (µm)', 'e_sacci_size');
$form->addInput('number', 'e_sacci_size', '', 'E sacci size ', '');
$form->endDependentFields();

/*$form->startDependentFields('grain_morphology_pollen[]', 'monoporate,diporate,dicolporate,triporate,tricolporate,4 porate,4 colporate,5 porate,5 colporate,zonoporate,pantoporate,pantocolporate,syncopate / syncolporate,parasyncolpate / parasyncolporate' ); # if 3.b = any of the following 3.b.vi 3.b.ix 3.b.xii 3.b.xv 3.b.xviii 3.b.xxi 3.b.xxiv 3.b.xxv
$form->endDependentFields();*/

//$form->endDependentFields(); # For Apertures

$form->endFieldset();

#######################
# Plan Functional Group
#######################
$form->startFieldset('Plan Functional Group');
$form->addOption('plant_function_type[]', 'Tree/Shrub (Gymnosperm) (TRSH Gym)',  'Tree/Shrub (Gymnosperm) (TRSH Gym)', '', '');
$form->addOption('plant_function_type[]', 'Tree/Shrub (Angiosperm) (TRSH An)',  'Tree/Shrub (Angiosperm) (TRSH An)', '', '');
$form->addOption('plant_function_type[]', 'Liana (L)',  'Liana (L)', '', '');
$form->addOption('plant_function_type[]', 'Herb (Dry) (HERB D)',  'Herb (Dry) (HERB D)', '', '');
$form->addOption('plant_function_type[]', 'Herb (Wet) (HERB W)',  'Herb (Wet) (HERB W)', '', '');
$form->addOption('plant_function_type[]', 'Pteridophyte (PTER)',  'Pteridophyte (PTER)', '', '');
$form->addHelper('Multiple Choice', 'plant_function_type[]');
$form->addSelect('plant_function_type[]', 'Plan Function Type ', 'class=select2, data-width=100%, multiple=multiple');
$form->endFieldset();

#######################
# Images
#######################
$form->startFieldset('Images');
$current_file = array(); // default empty
$fileUpload_config = array(
    'xml'           => 'image-upload', // the thumbs directories must exist
    'uploader'      => 'ajax_upload_image.php', // the uploader file in phpformbuilder/plugins/fileuploader/[xml]/php
    'upload_dir'    => '../../../../images/uploads/', // the directory to upload the files. relative to [plugins dir]/fileuploader/image-upload/php/ajax_upload_file.php
    'limit'         => 10, // max. number of files
    'file_max_size' => 3, // each file's maximal size in MB {null, Number}
    'extensions'    => ['jpg', 'jpeg', 'png'],
    'thumbnails'    => true,
    'editor'        => true,
    'width'         => 960,
    'height'        => 720,
    'crop'          => false,
    'debug'         => true
);

if(!is_dir('/var/www/html/phpformbuilder/images/uploads/'.$_GET["project_id"] . "/" . $_GET["specimen_id"])) {
    mkdir('/var/www/html/phpformbuilder/images/uploads/'.$_GET["project_id"] . "/" . $_GET["specimen_id"], 0777, true);
    umask(0);
}


if ($_GET["edit"]) {
    $current_file_path = '/var/www/html/phpformbuilder/images/uploads/'.$_GET["project_id"].'/'.$_GET["specimen_id"].'/';
    if (file_exists($current_file_path)) {
        $dir = new DirectoryIterator($current_file_path);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $current_file_name = $fileinfo->getFilename();
                if ($current_file_name != "thumbnail") {
                    if (file_exists($current_file_path . $current_file_name)) {
                        $current_file_size = filesize($current_file_path . $current_file_name);
                        $current_file_type = mime_content_type($current_file_path . $current_file_name);
                        $current_file[] = array(
                            'name' => $current_file_name,
                            'size' => $current_file_size,
                            'type' => $current_file_type,
                            'file' => '/phpformbuilder/images/uploads/' . $_GET["project_id"] . '/' . $_GET["specimen_id"] . "/" . $current_file_name, // url of the file
                            'data' => array(
                                'listProps' => array(
                                    'file' => $current_file_name
                                )
                            )
                        );
                    }
                }
            }
        }


        $fileUpload_config = array(
            'xml' => 'image-upload', // the thumbs directories must exist
            'uploader' => 'ajax_upload_image.php', // the uploader file in phpformbuilder/plugins/fileuploader/[xml]/php
            'upload_dir' => '../../../../images/uploads/' . $_GET["project_id"] . '/' . $_GET["specimen_id"] . '/', // the directory to upload the files. relative to [plugins dir]/fileuploader/image-upload/php/ajax_upload_file.php
            'limit' => 10, // max. number of files
            'file_max_size' => 3, // each file's maximal size in MB {null, Number}
            'extensions' => ['jpg', 'jpeg', 'png'],
            'thumbnails' => true,
            'editor' => true,
            'width' => 960,
            'height' => 720,
            'crop' => false,
            'debug' => true
        );
    }
}

$form->addHelper('Primary image first. Accepted File Types : Accepted File Types : .jp[e]g, .png, .gif', 'uploaded-images', 'after');
$form->addFileUpload('file', 'uploaded-images', '', 'Upload up to 10 images', '', $fileUpload_config, $current_file);
$form->endFieldset();

#######################
# Description
#######################
$form->startFieldset('Notes');
$form->addPlugin('tinymce', '#morphology_notes', 'contact-config');
$form->addTextarea('morphology_notes', '', 'Grain Morphology Notes');
$form->endFieldset();

#######################
# Clear/Save
#######################
$form->addBtn('submit', 'submit-btn', "save", '<i class="fa fa-save" aria-hidden="true"></i> Save', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, '<i class="fa fa-ban" aria-hidden="true"></i> Reset', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'delete-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this specimen? Note: it will also be deleted from any samples it is connected to.\')', 'my-btn-group');
}

$form->printBtnGroup('my-btn-group');

// Custom radio & checkbox css
$form->addPlugin('nice-check', 'form', 'default', ['%skin%' => 'purple']);

// jQuery validation
$form->addPlugin('formvalidation', '#add-new', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
if (isset($_GET["edit"])) {
    $page_render->setPageRestrictions(true, true, false, false);
} else {
    $page_render->setPageRestrictions(true, true, false, false);
}
$page_render->renderPage();
?>
<style>
    .select2-highlight {
        -webkit-box-shadow: 0px 0px 3px 3px rgba(0,155,212,1);
        -moz-box-shadow: 0px 0px 3px 3px rgba(0,155,212,1);
        box-shadow: 0px 0px 3px 3px rgba(0,155,212,1);
    }

    .select2 {
        transition: box-shadow 500ms;
    }
</style>
<script>
    function add_data_point(axis_type) {
        new_data_point_value = document.getElementById(axis_type+"_axis_input").value;
        new_data_point_float = parseFloat(new_data_point_value);
        if (!new_data_point_value || new_data_point_float <= 0) {
            alert("Please enter a positive number");
            return;
        }

        // If no data points added yet
        if (!document.getElementById(axis_type+"_axis_n").value) {
            var n = 0;
            var avg = 0;
            var min = Infinity;
            var max = -Infinity;
        } else {
            var n = parseFloat(document.getElementById(axis_type+"_axis_n").value);
            var avg = parseFloat(document.getElementById(axis_type+"_axis_length_avg").value);
            var min = parseFloat(document.getElementById(axis_type+"_axis_length_min").value);
            var max = parseFloat(document.getElementById(axis_type+"_axis_length_max").value);
        }
        var total = avg * n;
        if (new_data_point_float < min) {
            document.getElementById(axis_type+"_axis_length_min").value = new_data_point_float;
        }
        if (new_data_point_float > max) {
            document.getElementById(axis_type+"_axis_length_max").value = new_data_point_float;
        }
        document.getElementById(axis_type+"_axis_length_avg").value = (total + new_data_point_float) / (n + 1);
        document.getElementById(axis_type+"_axis_n").value = n + 1;

        // If polar and equatorial data points exist
        if (document.getElementById("polar_axis_n").value && document.getElementById("equatorial_axis_n").value) {
            var p = parseFloat(document.getElementById("polar_axis_length_max").value);
            var e = parseFloat(document.getElementById("equatorial_axis_length_max").value);
            update_size(p,e);
            update_equatorial_shape_major(p,e);
        }


    }

    function reset_data(axis_type) {
        document.getElementById(axis_type+"_axis_length_min").value = null;
        document.getElementById(axis_type+"_axis_length_avg").value = null;
        document.getElementById(axis_type+"_axis_length_max").value = null;
        document.getElementById(axis_type+"_axis_n").value = null;
    }

    function update_size(p,e) {
        var select = $("#size");
        var old_val = select.val();
        var max = Math.max(p,e);
        if (max < 10) {
            select.val('very small').trigger('change');
        } else if (max <25) {
            select.val('small').trigger('change');
        } else if (max <50) {
            select.val('medium').trigger('change');
        } else if (max <100) {
            select.val('large').trigger('change');
        } else if (max <200) {
            select.val('very large').trigger('change');
        } else {
            select.val('gigantic').trigger('change');
        }

        var new_val = select.val();
        if (old_val != new_val) {
            select.next().addClass("select2-highlight");
            setTimeout(function() {
                select.next().removeClass("select2-highlight");
            }, 500)
        }
    }

    function update_equatorial_shape_major(p,e) {
        var select = $("#equatorial_shape_major");
        var old_val = select.val();
        var p_e = p/e;
        if (p_e < 0.5) {
            select.val('peroblate').trigger('change');
        } else if (p_e < 0.76) {
            select.val('oblate').trigger('change');
        } else if (p_e < 0.89) {
            select.val('suboblate').trigger('change');
        } else if (p_e < 0.99) {
            select.val('oblate-spheroidal').trigger('change');
        } else if (p_e < 1.01) {
            select.val('spheroidal').trigger('change');
        } else if (p_e < 1.15) {
            select.val('prolate-spheroidal').trigger('change');
        } else if (p_e < 1.34) {
            select.val('subprolate').trigger('change');
        } else if (p_e < 2.00) {
            select.val('prolate').trigger('change');
        } else {
            select.val('perprolate').trigger('change');
        }

        var new_val = select.val();
        if (old_val != new_val) {
            select.next().addClass("select2-highlight");
            setTimeout(function() {
                select.next().removeClass("select2-highlight");
            }, 500)
        }

    }
</script>
