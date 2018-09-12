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

$project = $_GET["project"];

$db = new Mysql();

/* =============================================
    validation if posted
============================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('register') === true) {
    $validator = Form::validate('register');
    //$validator->recaptcha($_POST["captcha_code"], 'Recaptcha Error')->validate('g-recaptcha-response');

    if ($validator->hasErrors()) {
        $_SESSION['errors']['register'] = $validator->getAllErrors();
    } else {
        if ($_POST["password"] == $_POST["password_conf"]) {
            $update["username"] = Mysql::SQLValue($_POST["username"]);
            $update['first_name'] = Mysql::SQLValue($_POST["first_name"]);
            $update['last_name'] = Mysql::SQLValue($_POST["last_name"]);
            $update["email"] = Mysql::SQLValue($_POST["email"]);
            $update['institution'] = Mysql::SQLValue($_POST["institution"]);
            // Create encrypted password
            $update["password"] = Mysql::SQLValue(password_hash($_POST["password"], PASSWORD_DEFAULT));


            $db->insertRow('users', $update);

            if (!empty($db->error())) {
                $msg = '<p class="alert alert-danger">' . $db->error() . '<br>' . $db->getLastSql() . '</p>' . "\n";
            } else {
                $msg = '<p class="alert alert-success">User: '.$_POST["username"].' added successfully !</p>' . " \n";
            }
        } else {
            $msg = '<p class="alert alert-danger">Passwords do not match</p>' . "\n";
        }
    }
}

/* ==================================================
    The Form
================================================== */
unset($_SESSION['register']);


$form = new Form('register', 'horizontal', 'novalidate', 'bs4');

$form->setCols(0, 12);

$form->addHelper('Username', 'username');
$form->addInput('text', 'username', '', '', 'required');

$form->addHelper('First Name', 'first_name');
$form->addInput('text', 'first_name', '', '', 'required');

$form->addHelper('Last Name', 'last_name');
$form->addInput('text', 'last_name', '', '', 'required');

$form->addHelper('Email', 'email');
$form->addInput('email', 'email', '', '', 'required, placeholder=Email');

$form->addHelper('Your Institution/Company', 'institution');
$form->addInput('text', 'institution', '', '');

$form->addHelper('Password', 'password');
$form->addInput('password', 'password', '', '', 'required, data-fv-stringlength, data-fv-stringlength-min=6, data-fv-stringlength-message=Your password must be at least 6 characters long');

$form->addHelper('Password Confirmation', 'password_conf');
$form->addInput('password', 'password_conf', '', '', 'required, data-fv-stringlength, data-fv-stringlength-min=6, data-fv-stringlength-message=Your password must be at least 6 characters long');

// Captcha if we decide to allow any user to register
/*$key = rand_char(15);
$form->addHtml('<input id="captcha_code" name="captcha_code" type="hidden" value="'.$key.'">');
$form->addRecaptcha($key);*/

#######################
# Clear/Save
#######################
$form->addBtn('submit', 'submit-btn', 1, 'Register User <i class="fa fa-save" aria-hidden="true"></i>', 'class=btn btn-success ladda-button, data-style=zoom-in', 'my-btn-group');
$form->addBtn('reset', 'reset-btn', 1, 'Reset <i class="fa fa-ban" aria-hidden="true"></i>', 'class=btn btn-warning, onclick=confirm(\'Are you sure you want to reset all fields?\')', 'my-btn-group');
if ($_GET["edit"]) {
    $form->addBtn('submit', 'submit-btn', "delete", 'Delete <i class="fa fa-trash" aria-hidden="true"></i>', 'class=btn btn-danger, onclick=return confirm(\'Are you sure you want to delete this core?\')', 'my-btn-group');
}
$form->printBtnGroup('my-btn-group');

// jQuery validation
$form->addPlugin('formvalidation', '#register', 'bs4');

// Render Page
$page_render = new \classes\Page_Renderer();
$page_render->setForm($form);
$page_render->setPageTitle("Register");
$page_render->disableSidebar();
$page_render->disableNavbar();
$page_render->noLoginRequired();
$page_render->disableSidebar();
$page_render->renderPage();