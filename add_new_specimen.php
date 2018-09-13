<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 5/03/2018
 * Time: 3:34 PM
 */

use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

use function functions\printDbErrors;
use classes\Abstract_Add_New_Form;

require_once "classes/Page_Renderer.php";
require_once "classes/Abstract_Add_New_Form.php";

function delete_files($target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

        foreach( $files as $file )
        {
            delete_files( $file );
        }

        rmdir( $target );
    } elseif(is_file($target)) {
        unlink( $target );
    }
}

function extract_name($file) {
    $file_array = explode("/", $file);
    return empty($file_array[1]) ? $file_array[0] : $file_array[1];
}

$image_folder = $_SERVER['DOCUMENT_ROOT']."/phpformbuilder/images/uploads/";
if (!file_exists($image_folder)) {
    mkdir($image_folder);
}


class Specimen_Form extends Abstract_Add_New_Form {
    public function setFormType() {
        $this->form_type = "specimen";
    }

    protected function delete() {
        parent::delete();
        if (!$this->db->error()) {
            global $image_folder;
            delete_files($image_folder . $_POST["specimen_id"]. "/");
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
                $update_found["last_update"] = "'" . date("Y-m-d H:i:s") . "'";
                $update_found["count"] = Mysql::SQLValue(1);
                $this->db->insertRow('found_specimens', $update_found);
                printDbErrors($this->db);

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
                printDbErrors($this->db, "Specimen successfully added to project and to sample!");
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
        $update["polar_axis_length"] = Mysql::SQLValue($_POST["polar_axis_length"], "float");
        $update["polar_axis_n"] = Mysql::SQLValue($_POST["polar_axis_n"], "float");
        $update["equatorial_axis_length"] = Mysql::SQLValue($_POST["equatorial_axis_length"], "float");
        $update["equatorial_axis_n"] = Mysql::SQLValue($_POST["equatorial_axis_n"], "float");
        //$update["***determined***"] = Mysql::SQLValue($_POST["***determined***"], "text");
        //$update["***determined***"] = Mysql::SQLValue($_POST["***determined***"], "text");
        $update["equatorial_shape_minor"] = Mysql::SQLValue($_POST["equatorial_shape"], "text");
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
            $update["image_folder"] = Mysql::SQLValue($image_folder . $_POST["specimen_id"] . "/", "text");
            mkdir($image_folder . $_POST["specimen_id"]);
            $uploaded_images = json_decode($_POST["uploaded-images"], true);
            $update["primary_image"] = Mysql::SQLValue(extract_name($uploaded_images[0]["file"]), "text");
            foreach ($uploaded_images as $image) {
                $image = extract_name($image["file"]);
                //echo $image_folder.$image, $image_folder.$_POST["specimen_id"]."/".$image;
                rename($image_folder . $image, $image_folder . $_POST["specimen_id"] . "/" . $image);
            }
        }

        $this->update = $update;
    }

    protected function fillFormWithDbValues($record_array) {
        $specimen = $record_array;
        $_SESSION[$this->form_name]["specimen_id"] = $specimen["specimen_id"];
        $_SESSION[$this->form_name]["family"] = $specimen["family"];
        $_SESSION[$this->form_name]["genus"] = $specimen["genus"];
        $_SESSION[$this->form_name]["species"] = $specimen["species"];
        $_SESSION[$this->form_name]["poll_spore"] = $specimen["poll_spore"];
        $_SESSION[$this->form_name]["grain_arrangement"] = $specimen["grain_arrangement"];
        $_SESSION[$this->form_name]["grain_morphology_" . $specimen["poll_spore"]] = explode(",", $specimen["grain_morphology"]); // poll / spore
        $_SESSION[$this->form_name]["polar_axis_length"] = $specimen["polar_axis_length"];
        $_SESSION[$this->form_name]["polar_axis_n"] = $specimen["polar_axis_n"];
        $_SESSION[$this->form_name]["equatorial_axis_length"] = $specimen["equatorial_axis_length"];
        $_SESSION[$this->form_name]["equatorial_axis_n"] = $specimen["equatorial_axis_n"];
        //$_SESSION[$this->form_name]["***determined***"] = $specimen["***determined***"];
        //$_SESSION[$this->form_name]["***determined***"] = $specimen["***determined***"];
        $_SESSION[$this->form_name]["equatorial_shape_minor"] = $specimen["equatorial_shape"];
        $_SESSION[$this->form_name]["polar_shape"] = explode(",", $specimen["polar_shape"]);
        $_SESSION[$this->form_name]["surface_pattern"] = $specimen["surface_pattern"];
        $_SESSION[$this->form_name]["wall_thickness"] = $specimen["wall_thickness"];
        $_SESSION[$this->form_name]["wall_evenness"] = $specimen["wall_evenness"];
        $_SESSION[$this->form_name]["exine_type"] = $specimen["exine_type"];
        $_SESSION[$this->form_name]["colporus"] = $specimen["colporus"];
        $_SESSION[$this->form_name]["L_P"] = $specimen["L_P"];
        $_SESSION[$this->form_name]["L_E"] = $specimen["L_E"];
        $_SESSION[$this->form_name]["pore_protrusion"] = $specimen["pore_protrusion"];
        $_SESSION[$this->form_name]["pore_shape_e"] = $specimen["pore_shape_e"];
        $_SESSION[$this->form_name]["pore_shape_size"] = $specimen["pore_shape_size"];
        $_SESSION[$this->form_name]["pore_shape_p"] = $specimen["pore_shape_p"];
        $_SESSION[$this->form_name]["pore_margin"] = $specimen["pore_margin"];
        $_SESSION[$this->form_name]["colpus_sulcus_length_c_" . $specimen["poll_spore"]] = $specimen["colpus_sulcus_length_c"]; // poll / spore
        $_SESSION[$this->form_name]["colpus_sulcus_shape_" . $specimen["poll_spore"]] = $specimen["colpus_sulcus_shape"]; // poll / spore
        //$_SESSION[$this->form_name]["***determined***"] = $specimen["***determined***"];
        $_SESSION[$this->form_name]["colpus_sulcus_margin_" . $specimen["poll_spore"]] = $specimen["colpus_sulcus_margin"]; // poll / spore
        $_SESSION[$this->form_name]["apocolpium_width_e"] = $specimen["apocolpium_width_e"];
        //$_SESSION[$this->form_name]["***determined***"] = $specimen["***determined***"];
        $_SESSION[$this->form_name]["trilete_scar_arm_length"] = $specimen["trilete_scar_arm_length"];
        $_SESSION[$this->form_name]["trilete_scar_shape"] = $specimen["trilete_scar_shape"];
        $_SESSION[$this->form_name]["p_sacci_size"] = $specimen["p_sacci_size"];
        $_SESSION[$this->form_name]["e_sacci_size"] = $specimen["e_sacci_size"];
        $_SESSION[$this->form_name]["plant_function_type"] = explode(",", $specimen["plant_function_type"]);
        $_SESSION[$this->form_name]["morphology_notes"] = $specimen["morphology_notes"];
    }
}

$specimen_form = new Specimen_Form();


/* ==================================================
    The Form
================================================== */

$form = new Form($specimen_form->getFormName(), 'horizontal', 'novalidate', 'bs4');

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
$form->addHelper('Specimen ID', 'specimen_id');
if ($_GET["edit"]) {
    $form->addInput('text', 'specimen_id', '', 'Descriptors ', 'required, readonly="readonly"');
} else {
    $form->addInput('text', 'specimen_id', '', 'Descriptors ', 'required'); // Need to have warning for code !!!!!!!!!
}
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
$form->setCols(4, 8);

# Type
$form->addHelper('Pollen/Spore', 'poll_spore');
$form->addOption('poll_spore', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('poll_spore', 'pollen', 'Pollen', '', '');
$form->addOption('poll_spore', 'spore', 'Spore', '', '');
$form->addSelect('poll_spore', 'Type', 'class=select2, data-width=100%, required');

$form->endFieldset();
$form->startFieldset('Morphology');

# 2. Grain Arrangement
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

# 4. & 5. Lengths
# Polar
$form->setCols(4, 4);
$form->groupInputs('polar_axis_length', 'polar_axis_n');
$form->addHelper('Average in (µm) Note: Rounded to 1dp upon save', 'polar_axis_length');
$form->addInput('number', 'polar_axis_length', '', 'Polar axis length ', 'readonly="readonly"');
$form->addHelper('Number of measurements', 'polar_axis_length');
$form->addHelper('Number of measurements', 'polar_axis_n');
$form->addInput('number', 'polar_axis_n', '', '', 'readonly="readonly"');

$form->groupInputs('polar_axis_input', 'polar_axis_button');
$form->addHelper('Input in (µm)', 'polar_axis_input');
$form->addInput('number', 'polar_axis_input', '', '', '');
$form->addBtn('button', 'polar_axis_button', "merge", 'Merge with average <i class="fa fa-plus" aria-hidden="true"></i>', 'class=btn btn-success, data-style=zoom-in, onclick=merge_polar()', '');
$form->setCols(4, 8);

# Equatorial
$form->setCols(4, 4);
$form->groupInputs('equatorial_axis_length', 'equatorial_axis_n');
$form->addHelper('Average in (µm) Note: Rounded to 1dp upon save', 'equatorial_axis_length');
$form->addInput('number', 'equatorial_axis_length', '', 'Equatorial axis length ', 'readonly="readonly"');
$form->addHelper('Number of measurements', 'equatorial_axis_length');
$form->addHelper('Number of measurements', 'equatorial_axis_n');
$form->addInput('number', 'equatorial_axis_n', '', '', 'readonly="readonly"');

$form->groupInputs('equatorial_axis_input', 'equatorial_axis_button');
$form->addHelper('Input in (µm)', 'equatorial_axis_input');
$form->addInput('number', 'equatorial_axis_input', '', '', '');
$form->addBtn('button', 'equatorial_axis_button', "merge", 'Merge with average <i class="fa fa-plus" aria-hidden="true"></i>', 'class=btn btn-success, data-style=zoom-in, onclick=merge_equatorial()', '');
$form->setCols(4, 8);

# 6 & 7 can be determined

# 8. Equatorial shape (minor)
$form->addOption('equatorial_shape', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('equatorial_shape', 'rounded ', 'rounded ', '', '');
$form->addOption('equatorial_shape', 'rectangular', 'rectangular', '', '');
$form->addOption('equatorial_shape', 'rhombic', 'rhombic', '', '');
$form->addOption('equatorial_shape', 'triangular', 'triangular', '', '');
$form->addOption('equatorial_shape', 'bilateral', 'bilateral', '', '');
$form->addSelect('equatorial_shape', 'Equatorial shape ', 'class=select2, data-width=100%');

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


// NOT WORKING ATM
if ($_GET["edit"]) {
    $current_file_path = '/var/www/html/phpformbuilder/images/uploads/'.$_GET["specimen_id"].'/';
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
                            'file' => '/phpformbuilder/images/uploads/' . $_GET["specimen_id"] . "/" . $current_file_name, // url of the file
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
            'upload_dir' => '../../../../images/uploads/' . $_GET["specimen_id"] . '/', // the directory to upload the files. relative to [plugins dir]/fileuploader/image-upload/php/ajax_upload_file.php
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
$form->addBtn('submit', 'submit-btn', "save", 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
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
$page_render->setPageTitle($specimen_form->getPageTitle());
$page_render->renderPage();
?>
<script>
    function merge_polar (){
        if (!document.getElementById("polar_axis_n").value || !document.getElementById("polar_axis_length").value) {
            n_value = 0;
            current_total = 0;
        } else {
            n_value = parseFloat(document.getElementById("polar_axis_n").value);
            current_total = parseFloat(document.getElementById("polar_axis_length").value) * n_value;
        }
        document.getElementById("polar_axis_length").value = (current_total + parseFloat(document.getElementById("polar_axis_input").value)) / (n_value + 1);
        document.getElementById("polar_axis_n").value = n_value + 1;
    }

    function merge_equatorial (){
        if (!document.getElementById("equatorial_axis_n").value || !document.getElementById("polar_axis_length").value) {
            n_value = 0;
            current_total = 0;
        } else {
            n_value = parseFloat(document.getElementById("equatorial_axis_n").value);
            current_total = parseFloat(document.getElementById("equatorial_axis_length").value) * n_value;
        }
        document.getElementById("equatorial_axis_length").value = (current_total + parseFloat(document.getElementById("equatorial_axis_input").value)) / (n_value + 1);
        document.getElementById("equatorial_axis_n").value = n_value + 1;
    }
</script>
