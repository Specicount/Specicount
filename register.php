<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;
use function functions\storeErrorMsg;
use function functions\storeSuccessMsg;
use function functions\storeDbMsg;

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

    protected function additionalValidation() {
        $this->validator->email()->validate('email');
        $this->validator->hasUppercase()->hasLowercase()->hasNumber()->minLength(8)->validate('password');
        $this->validator->matches('password')->validate('password_conf');
        //TODO: Implement recaptcha
        //$this->validator->recaptcha($_POST["captcha_code"], 'Recaptcha Error')->validate('g-recaptcha-response');
    }

    protected function create() {
        $this->db->insertRow($this->table_name, $this->update);
        storeDbMsg($this->db,'User: ' . $_POST["email"] . ' added successfully!', "That email already exists!");
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

Form::clear("register");
$form = new Register_Form("register","users", 'horizontal', 'novalidate', 'bs4');

$form->setCols(3, 9);

$form->addInput('email', 'email', '', 'Email', 'required, class=col-5');
$form->addInput('text', 'first_name', '', 'First Name', 'required, class=col-5');
$form->addInput('text', 'last_name', '', 'Last Name', 'required, class=col-5');
$form->addInput('text', 'institution', '', 'Your Institution/Company', "class=col-5");
$form->addHelper("Must contain atleast 1 number, 1 uppercase letter, 1 lowercase letter and 1 symbol", "password");
$form->addInput('password', 'password', '', 'Password', 'required, class=col-5,
                data-fv-stringlength, data-fv-stringlength-min=8, data-fv-stringlength-message=Your password must be at least 8 characters long');
$form->addInput('password', 'password_conf', '', 'Password Confirmation', 'required, class=col-5,
                data-fv-stringlength, data-fv-stringlength-min=8, data-fv-stringlength-message=Your password must be at least 8 characters long');

$form->addRecaptcha('6Ldg0QkUAAAAABmXaV1b9qdOnyIwVPRRAs4ldoxe', 'recaptcha2', true);

$form->addBtn('submit', 'submit-btn', "save", '<i class="fa fa-user-plus" aria-hidden="true"></i> Register', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'delete-btn', "delete", '<i class="fa fa-trash" aria-hidden="true"></i> Delete', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this core?\')', 'my-btn-group');
}
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin("formvalidation","#".$form->getFormName(), "bs4");

// Captcha if we decide to allow any user to register


// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->noLoginRequired();
$page_render->disableSidebar();
$page_render->renderPage();