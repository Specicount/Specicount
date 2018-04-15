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

/* =============================================
    start session and include form class
============================================= */

/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

session_start();
include_once 'phpformbuilder/Form.php';
require_once 'phpformbuilder/database/db-connect.php';
require_once 'phpformbuilder/database/Mysql.php';

$project = $_GET["project"];
$core = $_GET["core"];
$sample = $_GET["sample"];
$date = date("Y-m-d H:i:s");

$image_folder = "/var/www/html/phpformbuilder/images/uploads/";
$db = new Mysql();

if ($_GET["edit"]) {
    $form_name = 'add-new-specimen-edit';
} else {
    $form_name = 'add-new-specimen';
}

/* =============================================
    validation if posted
============================================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($form_name) === true) {
    $validator = Form::validate($form_name);
    if ($validator->hasErrors()) {
        $_SESSION['errors']['user-form'] = $validator->getAllErrors();
    } else {
        $type = trim($_POST["poll_spore"]);
        $update["spec_id"] = Mysql::SQLValue($_POST["spec_id"], "text");
        $update["family"] = Mysql::SQLValue($_POST["family"], "text");
        $update["genus"] = Mysql::SQLValue($_POST["genus"], "text");
        $update["species"] = Mysql::SQLValue($_POST["species"], "text");
        $update["poll_spore"] = Mysql::SQLValue($type, "text");
        $update["grain_arrangement"] = Mysql::SQLValue($_POST["grain_arrangement"], "text");
        $update["grain_morphology"] = Mysql::SQLValue(implode(",", $_POST["grain_morphology_$type"]), "text"); // poll / spore
        $update["polar_axis_length"] = Mysql::SQLValue($_POST["polar_axis_length"], "float");
        $update["equatorial_axis_length"] = Mysql::SQLValue($_POST["equatorial_axis_length"], "float");
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
        $update["morphology_notes"] = Mysql::SQLValue($_POST["morphology_notes"], "text");

        if (!empty($_POST["uploaded-images"])) {
            $update["image_folder"] = Mysql::SQLValue($image_folder.$_POST["spec_id"]."/", "text");
            $update["primary_image"] = Mysql::SQLValue($_POST["uploaded-images"][0], "text");
            mkdir($image_folder.$_POST["spec_id"]);
            foreach ($_POST["uploaded-images"] as $image) {
                //echo $image_folder.$image, $image_folder.$_POST["spec_id"]."/".$image;
                rename($image_folder.$image, $image_folder.$_POST["spec_id"]."/".$image);
            }
            rename($image_folder."thumbnail", $image_folder.$_POST["spec_id"]."/thumbnail");
        }
        if ($_GET["edit"]) {
            $db->updateRows('specimen', $update, array("spec_id" => $update["spec_id"]));
        } else {
            $db->insertRow('specimen', $update);
        }
        if (!empty($db->error())) {
            $msg = '<p class="alert alert-danger">' . $db->error() . '</p>' . "\n";
        } else {
            $msg = '<p class="alert alert-success">Specimen updated successfully !</p>' . " \n";
            if (!$_GET["edit"]) {
                $update_found["spec_id"] = $update["spec_id"];
                $update_found["sample_id"] = Mysql::SQLValue($sample);
                $update_found["last_update"] = "'" . $date . "'";
                $update_found["count"] = Mysql::SQLValue(1);
                $db->insertRow('found_specimen', $update_found);
                //echo $db->getLastSQL();
                unset($_SESSION['add-new-specimen']);
                if ($db->error()) {
                    $msg = '<p class="alert alert-success">Specimen added successfully and added to sample !</p>' . " \n";
                }
            }
        }
    }
}

if ($_GET["edit"]) {
    unset($_SESSION['add-new-specimen-edit']);
    $spec = trim($_GET["edit"]);
    $db->selectRows('specimen', array('spec_id' => Mysql::SQLValue($spec)));
    $specimen = $db->recordsArray()[0];
    $_SESSION[$form_name]["spec_id"] = $specimen["spec_id"];
    $_SESSION[$form_name]["family"] = $specimen["family"];
    $_SESSION[$form_name]["genus"] = $specimen["genus"];
    $_SESSION[$form_name]["species"] = $specimen["species"];
    $_SESSION[$form_name]["poll_spore"] = $specimen["poll_spore"];
    $_SESSION[$form_name]["grain_arrangement"] = $specimen["grain_arrangement"];
    $_SESSION[$form_name]["grain_morphology_".$specimen["poll_spore"]] = explode(",", $specimen["grain_morphology"]); // poll / spore
    $_SESSION[$form_name]["polar_axis_length"] = $specimen["polar_axis_length"];
    $_SESSION[$form_name]["equatorial_axis_length"] = $specimen["equatorial_axis_length"];
    //$_SESSION[$form_name]["***determined***"] = $specimen["***determined***"];
    //$_SESSION[$form_name]["***determined***"] = $specimen["***determined***"];
    $_SESSION[$form_name]["equatorial_shape_minor"] = $specimen["equatorial_shape"];
    $_SESSION[$form_name]["polar_shape"] = explode(",", $specimen["polar_shape"]);
    $_SESSION[$form_name]["surface_pattern"] = $specimen["surface_pattern"];
    $_SESSION[$form_name]["wall_thickness"] = $specimen["wall_thickness"];
    $_SESSION[$form_name]["wall_evenness"] = $specimen["wall_evenness"];
    $_SESSION[$form_name]["exine_type"] = $specimen["exine_type"];
    $_SESSION[$form_name]["colporus"] = $specimen["colporus"];
    $_SESSION[$form_name]["L_P"] = $specimen["L_P"];
    $_SESSION[$form_name]["L_E"] = $specimen["L_E"];
    $_SESSION[$form_name]["pore_protrusion"] = $specimen["pore_protrusion"];
    $_SESSION[$form_name]["pore_shape_e"] = $specimen["pore_shape_e"];
    $_SESSION[$form_name]["pore_shape_size"] = $specimen["pore_shape_size"];
    $_SESSION[$form_name]["pore_shape_p"] = $specimen["pore_shape_p"];
    $_SESSION[$form_name]["pore_margin"] = $specimen["pore_margin"];
    $_SESSION[$form_name]["colpus_sulcus_length_c_".$specimen["poll_spore"]] = $specimen["colpus_sulcus_length_c"]; // poll / spore
    $_SESSION[$form_name]["colpus_sulcus_shape_".$specimen["poll_spore"]] = $specimen["colpus_sulcus_shape"]; // poll / spore
    //$_SESSION[$form_name]["***determined***"] = $specimen["***determined***"];
    $_SESSION[$form_name]["colpus_sulcus_margin_".$specimen["poll_spore"]] = $specimen["colpus_sulcus_margin"]; // poll / spore
    $_SESSION[$form_name]["apocolpium_width_e"] = $specimen["apocolpium_width_e"];
    //$_SESSION[$form_name]["***determined***"] = $specimen["***determined***"];
    $_SESSION[$form_name]["trilete_scar_arm_length"] = $specimen["trilete_scar_arm_length"];
    $_SESSION[$form_name]["trilete_scar_shape"] = $specimen["trilete_scar_shape"];
    $_SESSION[$form_name]["p_sacci_size"] = $specimen["p_sacci_size"];
    $_SESSION[$form_name]["e_sacci_size"] = $specimen["e_sacci_size"];
    $_SESSION[$form_name]["morphology_notes"] = $specimen["morphology_notes"];
}

/* ==================================================
    The Form
================================================== */

$form = new Form($form_name, 'horizontal', 'novalidate', 'bs4');

if ($sample) {
    $form->addHtml("<p style='font-style: italic'>Note: This will add the specimen to the website's database and also add it to the current sample: $sample</p>");
}

#######################
# Universal Fields
#######################

# Code ID and Family
$form->startFieldset('General');
$form->setCols(4, 4);
$form->groupInputs('spec_id', 'family');
$form->addHelper('Specimen ID', 'spec_id');
if ($_GET["edit"]) {
    $form->addInput('text', 'spec_id', '', 'Descriptors ', 'required, readonly="readonly"'); // Need to have warning for code !!!!!!!!!
} else {
    $form->addInput('text', 'spec_id', '', 'Descriptors ', 'required'); // Need to have warning for code !!!!!!!!!
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
/*$form->addCheckbox('grain_morphology_pollen', 'inaperturate',  'inaperturate');
$form->addCheckbox('grain_morphology_pollen', 'monoporate',  'monoporate');
$form->addCheckbox('grain_morphology_pollen', 'monocolpate / monosulcate',  'monocolpate / monosulcate');
$form->addCheckbox('grain_morphology_pollen', 'diporate',  'diporate');
$form->addCheckbox('grain_morphology_pollen', 'dicolpate',  'dicolpate');
$form->addCheckbox('grain_morphology_pollen', 'dicolporate',  'dicolporate');
$form->addCheckbox('grain_morphology_pollen', 'triporate',  'triporate');
$form->addCheckbox('grain_morphology_pollen', 'tricolpate',  'tricolpate');
$form->addCheckbox('grain_morphology_pollen', 'tricolporate',  'tricolporate');
$form->addCheckbox('grain_morphology_pollen', '4 porate',  '4 porate');
$form->addCheckbox('grain_morphology_pollen', '4 colpate',  '4 colpate');
$form->addCheckbox('grain_morphology_pollen', '4 colporate',  '4 colporate');
$form->addCheckbox('grain_morphology_pollen', '5 porate',  '5 porate');
$form->addCheckbox('grain_morphology_pollen', '5 colpate',  '5 colpate');
$form->addCheckbox('grain_morphology_pollen', '5 colporate',  '5 colporate');
$form->addCheckbox('grain_morphology_pollen', 'zonoporate',  'zonoporate');
$form->addCheckbox('grain_morphology_pollen', 'zonocolpate',  'zonocolpate');
$form->addCheckbox('grain_morphology_pollen', 'zonocolporate',  'zonocolporate');
$form->addCheckbox('grain_morphology_pollen', 'pantoporate',  'pantoporate');
$form->addCheckbox('grain_morphology_pollen', 'pantocolpate',  'pantocolpate');
$form->addCheckbox('grain_morphology_pollen', 'pantocolporate',  'pantocolporate');
$form->addCheckbox('grain_morphology_pollen', 'heterocolpate',  'heterocolpate');
$form->addCheckbox('grain_morphology_pollen', 'fenestrate',  'fenestrate');
$form->addCheckbox('grain_morphology_pollen', 'syncopate / syncolporate',  'syncopate / syncolporate');
$form->addCheckbox('grain_morphology_pollen', 'parasyncolpate / parasyncolporate',  'parasyncolpate / parasyncolporate');
$form->addCheckbox('grain_morphology_pollen', 'vesiculate / saccate',  'vesiculate / saccate');
$form->addCheckbox('grain_morphology_pollen', 'spiraperturate',  'spiraperturate');
$form->printCheckboxGroup('grain_morphology_pollen', 'Grain Morphology ', false, 'required');*/

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
$form->addHelper('Measurement in (µm)', 'polar_axis_length');
$form->addInput('number', 'polar_axis_length', '', 'Polar axis length ', '');
$form->addHelper('Measurement in (µm)', 'equatorial_axis_length');
$form->addInput('number', 'equatorial_axis_length', '', 'Equatorial axis length ', '');

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
$form->addSelect('polar_shape[]', 'Polar shape ', 'class=select2, data-width=100%, multiple=multiple, required');

/*$form->startFieldset('Single image with labels');
for ($i=0; $i < 10; $i++) {
    $form->addOption('polar_shape[]', 'https://www.phpformbuilder.pro/templates/assets/img/random-images/sports/sport-' . $i . '.jpg', '', '', 'data-img-src=https://www.phpformbuilder.pro/templates/assets/img/random-images/sports/sport-' . $i . '.jpg, data-img-label=Sport ' . $i . ', data-img-alt=Sport' . $i);
}
$form->addSelect('polar_shape[]', 'Choose your favourite sport', 'multiple, class=show_label, required');
$form->endFieldset();*/

# 10. *Surface pattern
$form->addOption('surface_pattern', '', 'Choose one ...', '', 'disabled, selected');
$form->addOption('surface_pattern', 'psilate',  'psilate', '', '');
$form->addOption('surface_pattern', 'granulate (rounded; <1 Measurement in (µm))',  'granulate (rounded; <1 Measurement in (µm))', '', '');
$form->addOption('surface_pattern', 'reticulate',  'reticulate', '', '');
$form->addOption('surface_pattern', 'baculate (length >1 Measurement in (µm); width <1 Measurement in (µm))',  'baculate (length >1 Measurement in (µm); width <1 Measurement in (µm))', '', '');
$form->addOption('surface_pattern', 'clavate',  'clavate', '', '');
$form->addOption('surface_pattern', 'gemmate',  'gemmate', '', '');
$form->addOption('surface_pattern', 'echinate',  'echinate', '', '');
$form->addOption('surface_pattern', 'fossulate (elongate, irregular)',  'fossulate (elongate, irregular)', '', '');
$form->addOption('surface_pattern', 'foveolate (>1 Measurement in (µm))',  'foveolate (>1 Measurement in (µm))', '', '');
$form->addOption('surface_pattern', 'perforate (<1 Measurement in (µm))',  'perforate (<1 Measurement in (µm))', '', '');
$form->addOption('surface_pattern', 'rugulate (elongate, irregular >1Measurement in (µm))',  'rugulate (elongate, irregular >1Measurement in (µm))', '', '');
$form->addOption('surface_pattern', 'striate (parallel)',  'striate (parallel)', '', '');
$form->addSelect('surface_pattern', 'Surface pattern ', 'class=select2, data-width=100%, required');

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
# Images
#######################
$form->startFieldset('Images');
$fileUpload_config = array(
    'xml'                 => 'images',
    'uploader'            => 'imageFileUpload.php',
    'btn-text'            => 'Browse ...',
    'max-number-of-files' => 10
);
$form->addHelper('Primary image first. Accepted File Types : .jp[e]g, .png, .gif', 'uploaded-images[]');
$form->addFileUpload('file', 'uploaded-images[]', '', 'Upload up to 10 images', '', $fileUpload_config);
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
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning', 'my-btn-group'); // !!!!!!!!!!
$form->addBtn('submit', 'submit-btn', 1, 'Save <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->printBtnGroup('my-btn-group');

// Custom radio & checkbox css
$form->addPlugin('nice-check', 'form', 'default', ['%skin%' => 'purple']);

// jQuery validation
$form->addPlugin('formvalidation', '#add-new', 'bs4');

//$form = array($form, $form_primary_image, $form_images);
$title = "Add New Specimen";
require_once "add_form_html.php";
