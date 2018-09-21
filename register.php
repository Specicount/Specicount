<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

// Captcha if we decide to allow any user to register
/*function rand_char($length) {
    $random = '';
    for ($i = 0; $i < $length; $i++) {
        $random .= chr(mt_rand(33, 126));
    }
    return $random;
}*/

require_once "classes/Page_Renderer.php";
require_once "classes/Post_Form.php";
require_once "page-components/functions.php";
use classes\Post_Form;

class Register_Form extends Post_Form {
    protected function setUpdateArray() {
        parent::setUpdateArray();
        // Create encrypted password
        $this->update["password"] = Mysql::SQLValue(password_hash($_POST["password"], PASSWORD_DEFAULT));
    }

    protected function create() {
        // TODO: Implement captcha
        //$this->validator->recaptcha($_POST["captcha_code"], 'Recaptcha Error')->validate('g-recaptcha-response');
        if ($_POST["password"] == $_POST["password_conf"]) {
            $this->db->insertRow($this->table_name, $this->update);
            $this->storeDbMsg('User: ' . $_POST["username"] . ' added successfully!', "Username already exists!");
        } else {
            $this->storeErrorMsg("Passwords do not match!");
        }
    }

    protected function update() {
        if ($_POST["password"] == $_POST["password_conf"]) {
            $this->db->updateRows($this->table_name, $this->update, $this->filter);
            $this->storeDbMsg('User: ' . $_POST["username"] . ' information updated!');
        } else {
            $this->storeErrorMsg("Passwords do not match!");
        }
    }

    protected function fillFormWithDbValues($record_array) {
        parent::fillFormWithDbValues($record_array);
        unset($_SESSION[$this->form_ID]["password"]);
    }
}

/* ==================================================
    The Form
================================================== */
//

Form::clear("register");
$form = new Register_Form("register","users", 'horizontal', 'novalidate', 'bs4');

$form->setCols(0, 12);

$form->addHelper('Username', 'username');
$form->addInput('text', 'username', '', '', 'required, class=col-4');

$form->addHelper('First Name', 'first_name');
$form->addInput('text', 'first_name', '', '', 'required, class=col-4');

$form->addHelper('Last Name', 'last_name');
$form->addInput('text', 'last_name', '', '', 'required, class=col-4');

$form->addHelper('Email', 'email');
$form->addInput('email', 'email', '', '', 'required, placeholder=Email, class=col-4');

$form->addHelper('Your Institution/Company', 'institution');
$form->addInput('text', 'institution', '', '', "class=col-4");

$form->addHelper('Password', 'password');
$form->addInput('password', 'password', '', '', 'required, data-fv-stringlength, data-fv-stringlength-min=6, data-fv-stringlength-message=Your password must be at least 6 characters long, class=col-4');

$form->addHelper('Password Confirmation', 'password_conf');
$form->addInput('password', 'password_conf', '', '', 'required, data-fv-stringlength, data-fv-stringlength-min=6, data-fv-stringlength-message=Your password must be at least 6 characters long, class=col-4');

// Captcha if we decide to allow any user to register
$form->addRecaptcha('6Ldg0QkUAAAAABmXaV1b9qdOnyIwVPRRAs4ldoxe', 'recaptcha2', true);

#######################
# Clear/Save
#######################
$form->addBtn('submit', 'submit-btn', "save", 'Register User <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'delete-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this core?\')', 'my-btn-group');
}
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#register', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->noLoginRequired();
$page_render->disableSidebar();
$page_render->renderPage();