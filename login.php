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

$at_login = true;
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

function doLogin()
{
    if ($_REQUEST['username'] && $_REQUEST['password']) {
        if (validLogin()) {
            $_SESSION['auth_user'] = $_REQUEST['username'];
        }
    }
}

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

function validLogin()
{
    $db = new Mysql();
    $db->selectRows('users', array('username' => Mysql::SQLValue($_REQUEST['username'])), null, null, true, 1);
    $user = $db->recordsArray()[0];

    if (password_verify($_REQUEST['password'], $user["passwd"])) {
        return TRUE;
    }
    return FALSE;
}

function renderLoginForm($errors = NULL)
{
    $myself = $_SERVER['PHP_SELF'];

    $errOut = NULL;

    if (is_array($errors) && !empty($errors)) {
        $errOut .= "<h2>Errors</h2>\n";

        $errOut .= "<ul class=\"errors\">\n";

        foreach ($errors as $error) {
            $errOut .= "<li>" . $error . "</li>\n";
        }

        $errOut .= "</ul>\n";
    }

    $username = isset($_REQUEST['username']) ? htmlentities($_REQUEST['username']) : NULL;
    $password = isset($_REQUEST['password']) ? htmlentities($_REQUEST['password']) : NULL;

    echo $errOut;
    $form = new Form("login", 'horizontal', 'novalidate', 'bs4');
    $form->setCols(0, 12);
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
    //$page_render->disableNavbar();
    $page_render->renderPage();
}