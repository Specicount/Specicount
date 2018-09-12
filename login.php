<?php
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 18/08/2018
 * Time: 8:03 AM
 */

require_once "classes/Page_Renderer.php";

$myself = $_SERVER['PHP_SELF'];

$errors = array();

unset($_SESSION['login']);

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $errors = validateLogin();

    if (empty($errors)) {
        doLogin();
        header("Location: index.php");
        exit;
    } else {
        renderLoginForm($errors);
    }
} else {
    renderLoginForm();
}

# Test the username and password
function doLogin()
{
    if ($_REQUEST['username'] && $_REQUEST['password']) {
        if (validLogin()) {
            $_SESSION['username'] = $_REQUEST['username'];
        }
    }
}

# Test username or password was entered
function validateLogin()
{
    $errors = array();

    if (!$_REQUEST['username']) {
        $errors['username'] = "Please enter a username";
    }

    if (!$_REQUEST['password']) {
        $errors['password'] = "Please enter a password";
    }

    if (empty($errors)) {
        if (!validLogin()) {
            $errors['username'] = "Incorrect username or password";
        }
    }

    return $errors;
}

# Test password in database
function validLogin()
{
    return TRUE;
    $db = new Mysql();
    $db->selectRows('users', array('username' => Mysql::SQLValue($_REQUEST['username'])), null, null, true, 1);
    $user = $db->recordsArray()[0];

    // Password is hashed
    if (password_verify($_REQUEST['password'], $user["password"])) {
        return TRUE;
    }
    return FALSE;
}

# Create login form
function renderLoginForm($errors = NULL)
{
    //$myself = $_SERVER['PHP_SELF'];

    $errOut = NULL;

    /*$username = isset($_REQUEST['username']) ? htmlentities($_REQUEST['username']) : NULL;
    $password = isset($_REQUEST['password']) ? htmlentities($_REQUEST['password']) : NULL;*/

    $form = new Form("login", 'horizontal', 'novalidate', 'bs4');
    $form->setCols(0, 12);

    if (!empty($errors) and is_array($errors)) $form->addHtml("<p class=\"alert alert-danger\">".$errors['username']."</p>");
    $form->addHelper("Username", "username");
    $form->addInput('text', 'username', '', '', 'required');
    $form->addHelper("Password", "password");
    $form->addInput('password', 'password', '', '', 'required');
    $form->addBtn('submit', 'submit-btn', 1, 'Submit', 'class=btn btn-success ladda-button, data-style=zoom-in');

    // Render Page
    $page_render = new \classes\Page_Renderer();
    $page_render->setForm($form);
    $page_render->setPageTitle("BioBase Login");
    $page_render->disableSidebar();
    $page_render->noLoginRequired();
    $page_render->disableNavbar();
    $page_render->renderPage();
}