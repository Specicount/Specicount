<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/* =============================================
    start session and include form class
============================================= */

require_once "classes/Page_Renderer.php";
require_once "classes/Post_Form.php";
require_once "page-components/functions.php";
use classes\Post_Form;

class Register_Form extends Post_Form {

    protected function setFilterArray() {
        $this->filter["email"] = Mysql::SQLValue($_SESSION["email"]);
    }

    protected function setUpdateArray() {
        parent::setUpdateArray();
        // Create encrypted password
        $this->update["password"] = Mysql::SQLValue(password_hash($_POST["password"], PASSWORD_DEFAULT));
    }

    protected function additionalValidation() {
        if ($this->post_actions["valid"]["create"] == true) {
            $this->validator->email()->validate('email');
            $this->validator->hasUppercase()->hasLowercase()->hasNumber()->minLength(8)->validate('password');
            $this->validator->matches('password')->validate('password_conf');
            $this->validator->recaptcha("6Ley73EUAAAAAP0HYqdRqckHE3dHRcD3X7iwdtQF", 'Recaptcha Error')->validate('g-recaptcha-response');
        }
        return true;
    }

    protected function update() {
        $filter = array();
        $filter["email"] = Mysql::sqlValue($_SESSION["email"]);
        $update = array();
        $update["first_name"] = Mysql::sqlValue($_POST["first_name"]);
        $update["last_name"] = Mysql::sqlValue($_POST["last_name"]);
        $update["institution"] = Mysql::sqlValue($_POST["institution"]);
        $this->db->updateRows("users", $update, $filter);
        storeDbMsg($this->db,"Successfully updated details!");
    }

    protected function create() {
        $this->db->insertRow($this->table_name, $this->update);
        storeDbMsg($this->db,'User: ' . $_POST["email"] . ' added successfully!', "That email already exists!");
    }

    protected function fillFormWithDbValues($record_array) {
        parent::fillFormWithDbValues($record_array);
    }
}

/* ==================================================
    The Form
================================================== */


$form = new Register_Form("register","users", 'horizontal', 'novalidate', 'bs4');


$form->setCols(3, 9);

if (!isset($_GET["edit"])) {
    unset($_SESSION[$form->getFormName()]);
    $form->addInput('email', 'email', '', 'Email', 'required, class=col-5');
}
$form->addInput('text', 'first_name', '', 'First Name', 'required, class=col-5');
$form->addInput('text', 'last_name', '', 'Last Name', 'required, class=col-5');
$form->addInput('text', 'institution', '', 'Your Institution/Company', "class=col-5");

if (!isset($_GET["edit"])) {
    $form->addHelper("Must contain atleast 1 number, 1 uppercase letter, 1 lowercase letter", "password");
    $form->addInput('password', 'password', '', 'Password', 'required, class=col-5,
                data-fv-stringlength, data-fv-stringlength-min=8, data-fv-stringlength-message=Your password must be at least 8 characters long');
    $form->addInput('password', 'password_conf', '', 'Password Confirmation', 'required, class=col-5,
                data-fv-stringlength, data-fv-stringlength-min=8, data-fv-stringlength-message=Your password must be at least 8 characters long');

    $form->addRecaptcha('6Ley73EUAAAAAGlW8U8cgkYJ6k7fIDbTF5Am47Qj', 'recaptcha2', true);
}
if ($_GET["edit"]) {
    $form->addBtn('submit', 'submit-btn', "save", '<i class="fa fa-save" aria-hidden="true"></i> Save', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
} else {
    $form->addBtn('submit', 'submit-btn', "save", '<i class="fa fa-user-plus" aria-hidden="true"></i> Register', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
}
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin("formvalidation","#".$form->getFormName(), "bs4");

// Captcha if we decide to allow any user to register


// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
if ($_GET["edit"]) {
    $page_render->setPageRestrictions(true);
} else {
    $page_render->setPageRestrictions(false);
}
$page_render->disableSidebar();
$page_render->renderPage();