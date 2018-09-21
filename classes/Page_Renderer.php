<?php
/**
 * Created by IntelliJ IDEA.
 * User: Elliott
 * Date: 25/08/2018
 * Time: 3:14 PM
 */

/***
 * Class Page_Renderer
 * This class provides several options for dynamically rendering a page
 * It can render
 *      1. Anything in the /page_components directory
 *      2. Any PHPFormBuilder form
 *      3.
 *
 */

namespace classes;

use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;
use function functions\getTopMostScript;

// Add required files
$current_dir = __DIR__;
include_once $current_dir.'/../phpformbuilder/Form.php';
require_once $current_dir.'/../phpformbuilder/database/Mysql.php';
require_once $current_dir.'/../phpformbuilder/database/db-connect.php';
require_once $current_dir.'/../page-components/functions.php';
require_once $current_dir.'/../page-components/functions.php';

session_start();
// Test if the user is logged in

class Page_Renderer {

    private $page_title = "UNTITLED";

    //Whether the page requires a user to be logged in to view its contents
    private $require_login;

    //Variables that will store content to render on the page
    //Will only be rendered if not null
    private $form, $html_string, $render_header, $render_navbar, $render_sidebar, $render_scripts;

    // Archived: $php_filename


    public function __construct() {
        $this->render_header
        = $this->render_navbar
        = $this->render_sidebar
        = $this->render_scripts
        = $this->require_login
        = true;
    }

    public function noLoginRequired() {
        $this->require_login = false;
    }

    public function setPageTitle($title) {
        $this->page_title = $title;
    }

    public function disableHeader() {
        $this->render_header = false;
    }

    public function disableNavbar() {
        $this->render_navbar = false;
    }

    public function disableSidebar() {
        $this->render_sidebar = false;
    }

    public function disableScripts() {
        $this->render_scripts = false;
    }

    public function setForm($form) {
        $this->form = $form;
    }

    // Set the inner html of the page
    public function setInnerHTML($html_string) {
        $this->html_string = $html_string;
    }

    // Print CSS files of form
    protected function printCSS($form) {
        if ($form) {
            //Since form could be an array that holds multiple form objects
            if (is_array($form)) {
                foreach ($form as $f) {
                    $f->printIncludes('css');
                }
                //Otherwise it's just a single form object
            } else {
                $form->printIncludes('css');
            }
        }
    }

    // Print script files of form
    protected function printScripts($form){
        if ($form) {
            //Since form could be an array that holds multiple form objects
            if (is_array($form)) {
                foreach ($form as $f) {
                    $f->printIncludes('js');
                    $f->printJsCode();
                }
                //Otherwise it's just a single form object
            } else {
                $form->printIncludes('js');
                $form->printJsCode();
            }
        }
    }

    // Render form
    protected function renderForm($form) {
        if ($form) {
            //Since form could be an array that holds multiple form objects
            if (is_array($form)) {
                foreach ($form as $f) {
                    $f->render();
                }
                //Otherwise it's just a single form object
            } else {
                $form->render();
            }
        }
    }

    // Create the page
    public function renderPage() {

        $db = new Mysql();

        // Get current folder
        $current_dir = __DIR__;

        // True if user is able to access page
        $page_access = true;

        // Login form modal variable
        $login_form = null;

        // Test if user is logged in
        if (!isset($_SESSION['username'])) {
            $page_access = !$this->require_login;

            // Login error messages
            if (isset($_POST['do-login'])) {
                if (!$_POST['username']) {
                    $errors['username'] = "Please enter a username";
                    $login_msg = '<p class="alert alert-danger">Please enter a username</p>';
                }

                if (!$_POST['password']) {
                    $errors['password'] = "Please enter a password";
                    $login_msg = '<p class="alert alert-danger">Please enter a password</p>';
                }



                $db->selectRows('users', array('username' => Mysql::SQLValue($_POST['username'])), null, null, true, 1);
                $user = $db->recordsArray()[0];

                if (password_verify($_POST['password'], $user["password"])) {
                    $_SESSION['username'] = $_POST['username'];
                    if (basename(getTopMostScript(), ".php") == "register") {
                        header("location: index.php");
                    }
                } else {
                    $errors['username'] = "Incorrect username or password";
                    $login_msg = '<p class="alert alert-danger">Incorrect username or password</p>';
                }
            } else if ($this->require_login) {
                $login_msg = '<p class="alert alert-danger">You do not have access to this page</p>';
            }

            // Create form for login modal
            $login_form = new Form("login", 'horizontal', 'novalidate', 'bs4');
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

        // If trying to access a page connected to a project
        } else if ($_GET["project_id"]) {
            $filter["project_id"] = Mysql::SqlValue($_GET["project_id"]);
            $filter["username"] = Mysql::SqlValue($_SESSION["username"]);

            // If the user does not have access to that project
            if ($db->querySingleRowArray("user_project_access", $filter)) {
                // Redirect to home page
                $login_msg = '<p class="alert alert-danger">You do not have access to this page</p>';
                $page_access = !$this->require_login;
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title><?php
                if ($this->page_title == "UNTITLED" && isset($form)) {
                    echo $this->form->getPageTitle();
                }  else {
                    echo $this->page_title;
                }
            ?></title>
            <?php
            if ($this->render_header) {
                $render_side = $this->render_sidebar;
                require_once $current_dir.'/../page-components/header.php';
            }

            //Check whether $form has been initialised so we can add the relevant css files
            if ($login_form) $this->printCSS($login_form);
            if ($page_access) $this->printCSS($this->form);

            ?>
        </head>
        <?php
        //Make it so the text on the navbar reflects the title of the page
        //This variable is used in the navbar's php file
        $navbar_text = $this->page_title;
        if ($this->render_navbar) {
            require_once $current_dir.'/../page-components/navbar.php'; // Add top navbar
        }

        ?>

        <div class="d-flex">
            <?php
            if ($this->render_sidebar) {
                require_once $current_dir.'/../page-components/sidebar.php'; // Add sidebar
            }

            ?>
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div style="padding-top: 30px" class="col-lg-11 col-md-11 col-sm-11 col-xs-11">
                        <?php
                        //$sent_message refers to any message thrown by PHPFormBuilder
                        //$msg refers to any message thrown by the SQL query
                        global $sent_message, $msg;

                        // Print message from user


                        // Print login message
                        if (isset($login_msg)){
                            echo $login_msg;
                        }
                        if ($login_form) $this->renderForm($login_form);

                        // Don't render if user is not set
                        if ($page_access) {
                            if (isset($sent_message)) {
                                echo $sent_message;
                            } elseif (isset($msg)) {
                                echo $msg;
                            }
                            $this->renderForm($this->form);

                            //Check whether $html_string has been initialised so we can render
                            if ($this->html_string) {
                                echo $this->html_string;
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>


        <?php
        if ($this->render_scripts) {
            require_once $current_dir.'/../page-components/scripts.php'; // Get scripts
        }

        if ($login_form) $this->printScripts($login_form);
        if ($page_access) $this->printScripts($this->form);

        ?>
        </html>
    <?php
    }
}
?>
