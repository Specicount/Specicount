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
session_start();
include_once 'phpformbuilder/Form.php';
require_once 'phpformbuilder/database/db-connect.php';
require_once 'phpformbuilder/database/Mysql.php';

$myself = $_SERVER['PHP_SELF'];

$errors = array();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;

switch ($action) {
    case 'login.do':

        $errors = validateLogin();

        if (empty($errors)) {
            doLogin();

            header("Location: /billing/customers.php");
            exit;
        } else {
            renderLoginForm($errors);
        }

        break;

    default:

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
    /*$file = ".htpasswd";

    $lines = file($file);

    if (is_array($lines)) {
        foreach ($lines as $line) {
            list($user, $pass) = explode(':', $line);

            if ($user == $_REQUEST['username']) {
                $pass = trim($pass);

                $salt = substr($pass, 0, 2);

                if (crypt($_REQUEST['password'], $salt) == $pass) {
                    return TRUE;
                }
            }
        }
    }*/

    $db->selectRows('specimen', array('spec_id' => Mysql::SQLValue($_REQUEST['username'])), null, null, true, 1);
    $specimen = $db->recordsArray()[0];

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


    $form = new Form("login", 'horizontal', 'novalidate', 'bs4');
    $form->setCols(0, 12);
    $form->addInput('text', 'username', '', 'Username', 'required');
    $form->addInput('password', 'password', '', 'Password', 'required');
    $form->addBtn('submit', 'submit-btn', 1, 'Submit', 'class=btn btn-success ladda-button, data-style=zoom-in');
}