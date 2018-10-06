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

class Password_Reset_Form extends Post_Form {
    protected function setUpdateArray() {
        parent::setUpdateArray();
        // Create encrypted password
        $this->update["password"] = Mysql::SQLValue(password_hash($_POST["password"], PASSWORD_DEFAULT));
        $this->update["password_reset_code"] = "NULL";
    }

    protected function additionalValidation() {
        $this->validator->hasUppercase()->hasLowercase()->hasNumber()->minLength(8)->validate('password');
        $this->validator->matches('password')->validate('password_conf');
        $this->validator->recaptcha($_POST["captcha_code"], 'Recaptcha Error')->validate('g-recaptcha-response');
        return true;
    }

    protected function create() {
        $reset_code = $this->db->querySingleValue("SELECT password_reset_code FROM users WHERE email=".Mysql::SQLValue($_GET["email"]));
        if ($reset_code == $_GET["reset_code"]) {
            $this->db->updateRows("users", $this->update, array("email" => Mysql::SQLValue($_GET["email"])));
            storeDbMsg($this->db, 'User: ' . $_GET["email"] . ' updated successfully! Please try and login', "That email already exists!");
        } else {
            storeErrorMsg("You do not have the correct link, please try resetting your password again!");
        }
    }

    protected function update() {
        $this->raiseNotImplemented();
    }

    protected function fillFormWithDbValues($record_array) {
        parent::fillFormWithDbValues($record_array);
        unset($_SESSION[$this->form_ID]["password"]);
        unset($_SESSION[$this->form_ID]["password_conf"]);
    }
}

/* ==================================================
    The Form
================================================== */

$form = new Password_Reset_Form("password_reset", "users", 'horizontal', 'novalidate', 'bs4');

$form->setCols(3, 9);

$form->addHelper("Must contain atleast 1 number, 1 uppercase letter, 1 lowercase letter", "password");
$form->addInput('password', 'password', '', 'Password', 'required, class=col-5,
                data-fv-stringlength, data-fv-stringlength-min=8, data-fv-stringlength-message=Your password must be at least 8 characters long');
$form->addInput('password', 'password_conf', '', 'Password Confirmation', 'required, class=col-5,
                data-fv-stringlength, data-fv-stringlength-min=8, data-fv-stringlength-message=Your password must be at least 8 characters long');

$form->addRecaptcha('6Ley73EUAAAAAGlW8U8cgkYJ6k7fIDbTF5Am47Qj', 'recaptcha2', true);

$form->addBtn('submit', 'submit-btn', "save", '<i class="fa fa-user-plus" aria-hidden="true"></i> Update', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');

$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin("formvalidation","#".$form->getFormName(), "bs4");

// Captcha if we decide to allow any user to register


// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageAccess(false);
$page_render->disableSidebar();
$page_render->renderPage();