<?php
/**
 * Created by IntelliJ IDEA.
 * User: Elliott
 * Date: 21/09/2018
 * Time: 5:06 PM
 */

use phpformbuilder\Form;
use phpformbuilder\database\Mysql;
use classes\Post_Form;
use function functions\getTopMostScript;

require_once "classes/Page_Renderer.php";
require_once "classes/Post_Form.php";

class Login_Form extends Post_Form {
    protected function registerPostActions() {
        $this->registerPostAction("login", isset($_POST['do-login']));
    }

    protected function login() {
        if (!isset($_POST['username'])) {
            $this->storeErrorMsg("Please enter a username");
        } else if (!isset($_POST['password'])) {
            $this->storeErrorMsg("Please enter a password");
        } else {
            $filter['username'] = Mysql::SQLValue($_POST['username']);
            $this->db->selectRows($this->table_name, $filter, null, null, true, 1);
            $user = $this->db->recordsArray()[0];

            if (password_verify($_POST['password'], $user["password"])) {
                $_SESSION['username'] = $_POST['username'];
                if (basename(getTopMostScript(), ".php") == "register") {
                    header("location: index.php");
                }
            } else {
                $this->storeErrorMsg("Incorrect username or password");
            }
        }
    }
}

function getLoginForm() {
    $login_form = new Login_Form("login", "users", 'horizontal', 'novalidate', 'bs4');
    $login_form->startFieldset("Login");
    $login_form->setCols(0,12);
    $login_form->addInput("hidden", "do-login", 1);
    $login_form->addHelper("Username", "username");
    $login_form->addInput('text', 'username', '', '', 'required, class=col-4');
    $login_form->addHelper("Password", "password");
    $login_form->addInput('password', 'password', '', '', 'required, class=col-4');
    $login_form->addBtn('submit', 'submit-btn', "login", 'Log In', 'class=btn btn-success ladda-button, data-style=zoom-in');
    $login_form->addHtml('<a class="btn btn-primary" href="register.php"><i class="fa fa-user-plus"></i> Register Account</a>');
    $login_form->modal('#modal-login-target');
    $login_form->addPlugin('formvalidation', '#login', 'bs4');
    $login_form->endFieldset();
    Form::clear($login_form->getFormName());
    return $login_form;
}