<?php
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
use classes\Post_Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;


// Add required files
$current_dir = __DIR__;
include_once $current_dir.'/../phpformbuilder/Form.php';
require_once $current_dir.'/../classes/Post_Form.php';
require_once $current_dir.'/../phpformbuilder/database/Mysql.php';
require_once $current_dir.'/../phpformbuilder/database/db-connect.php';
require_once $current_dir.'/../page-components/functions.php';
require_once $current_dir.'/../page-components/components.php';

session_start();
// Test if the user is logged in

class Page_Renderer {

    private $page_title;

    //Whether the page requires a user to be logged in to view its contents
    private $require_login;

    //Variables that will store content to render on the page
    //Will only be rendered if not null
    private $form, $html_string, $render_header, $render_navbar, $render_sidebar, $render_scripts;

    // Determines whether the page will be rendered
    private $page_access = true;


    private $not_set = false;
    // Archived: $php_filename

    public function __construct() {
        $this->render_header
        = $this->render_navbar
        = $this->render_scripts
        = $this->require_login
        = $this->render_sidebar
        = true;
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

    /**
     * Set access for page
     * @param bool $require_login   whether the page requires a valid user login
     * @param bool $project         whether the page requires a valid project
     * @param bool $core            whether the page requires a valid core
     * @param bool $sample          whether the page requires a valid sample
     */
    public function setPageAccess($require_login = true, $project = false, $core = false, $sample = false) {

        $this->require_login = $require_login;

        $project_id = Mysql::SqlValue($_GET["project_id"]);
        $core_id = Mysql::SqlValue($_GET["core_id"]);
        $sample_id = Mysql::SqlValue($_GET["sample_id"]);

        $db = new Mysql();

        // If trying to access a sample
        if ($sample && !$db->querySingleValue("SELECT sample_id FROM samples WHERE project_id=$project_id AND core_id=$core_id AND sample_id=$sample_id")) {
            storeErrorMsg("Sample not found in database");
            $this->not_set = true;
        }
        // If trying to access a core
        else if ($core && !$db->querySingleValue("SELECT core_id FROM cores WHERE project_id=$project_id AND core_id=$core_id")){
            storeErrorMsg("Core not found in database");
            $this->not_set = true;
        }
        // If trying to access a project
        else if ($project && !$db->querySingleValue("SELECT project_id FROM projects WHERE project_id=$project_id")){
            storeErrorMsg("Project not found in database");
            $this->not_set = true;
        }

        // If trying to access any page connected to a project and the page's database equivalent exists
        if ($project && $this->page_access) {
            $my_access_level = getAccessLevel();
            // If the user does not have access to that project
            if (!$my_access_level) {
                $this->page_access = false;
            }
        }

        $this->page_access = !$this->not_set;
    }

    // Create the page
    public function renderPage() {

        // Use the form's default page title if a page title hasn't been explicitly set
        if (empty($this->page_title)) {
            if (isset($this->form)) {
                $this->page_title = $this->form->getPageTitle();
            } else {
                $this->page_title = "UNTITLED";
            }
        }

        // The following code can't be in setPageAccess because you can't rely on that function always being called
        // If user is not logged in
        if (!isset($_SESSION['email'])) {

            $db = new Mysql();

            $this->page_access = false;

            // Set login form for each page
            if (isset($_POST['do-login'])) {
                if (!$_POST['email']) {
                    $errors['email'] = "Please enter a email address";
                    storeErrorMsg('Please enter a email address');
                }

                if (!$_POST['password']) {
                    $errors['password'] = "Please enter a password";
                    storeErrorMsg('Please enter a password');
                }

                $db->selectRows('users', array('email' => Mysql::SQLValue($_POST['email'])), null, null, true, 1);
                $user = $db->recordsArray()[0];

                if (password_verify($_POST['password'], $user["password"])) {
                    $_SESSION['email'] = $_POST['email'];
                    $this->page_access = true;
                    if (basename(getTopMostScript(), ".php") == "register") {
                        header("location: index.php");
                    }
                    echo '<script>parent.window.location.reload();</script>';
                } else {
                    $errors['email'] = "Incorrect username or password";
                    storeErrorMsg('Incorrect username or password');
                }
            }

            // Login form
            $login_form = new Form("login", 'horizontal', 'novalidate', 'bs4');
            $login_form->startFieldset("Login");
            $login_form->setCols(0, 12);
            $login_form->addInput("hidden", "do-login", 1);
            $login_form->addHelper("Email", "email");
            $login_form->addInput('text', 'email', '', '', 'required');
            $login_form->addHelper("Password", "password");
            $login_form->addInput('password', 'password', '', '', 'required');
            $login_form->addHtml("<div class=\"btn-group\">");
            $login_form->addBtn('submit', 'submit-btn', "login", '<i class="fa fa-sign-in-alt"></i> Log In', 'class=btn btn-success ladda-button, data-style=zoom-in', 'lor-group');
            $login_form->addBtn('button', 'register-btn', "register", '<i class="fa fa-user-plus"></i> Register', 'class=btn btn-primary, data-style=zoom-in, onclick=navigate(\'register.php\')', 'lor-group');
            //$login_form->addHtml('<a class="btn btn-primary" href="register.php"><i class="fa fa-user-plus"></i> Register Account</a>');
            $login_form->printBtnGroup('lor-group');
            $login_form->modal('#modal-login-target');
            $login_form->addPlugin('formvalidation', '#login', 'bs4');
            $login_form->endFieldset();
            
            // Set the page accessible if it does not require login or we have verified the page access
            $this->page_access = !$this->require_login || $this->page_access;
        }

        // Page access should not be changed from here
        if (!$this->page_access && !$this->not_set) storeErrorMsg("You do not have access to this page");

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title><?=$this->page_title?></title>
            <?php
            if ($this->render_header) {
                echo getHeader();
            }

            //Check whether $form has been initialised so we can add the relevant css files
            if (isset($login_form)) $this->printCSS($login_form);
            if ($this->page_access) $this->printCSS($this->form);


            ?>
        </head>
        <?php
        //Make it so the text on the navbar reflects the title of the page
        //This variable is used in the navbar's php file
        if ($this->render_navbar) {
            echo getNavbar($this->render_sidebar, $this->page_title);
        }

        ?>

        <div class="d-flex">
            <?php
            // Render the sidebar
            if ($this->render_sidebar) {
                echo getSidebar();
            } else {
                echo "<style>.container-fluid {padding:0 10vw;}</style>";
            }

            ?>
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div style="padding-top: 30px" class="col-lg-11 col-md-11 col-sm-11 col-xs-11">
                        <?php
                        // $messages refers to our own message passing system that is mostly utilised by our own custom forms
                        global $messages;
                        if (isset($messages["error"])) {
                            $messages["error"] = array_unique($messages["error"]);
                            foreach ($messages["error"] as $error_msg) {
                                echo $error_msg;
                            }
                        } else if (isset($messages["success"])) {
                            $messages["success"] = array_unique($messages["success"]);
                            foreach ($messages["success"] as $success_msg) {
                                echo $success_msg;
                            }
                        }

                        // Render login form
                        if (isset($login_form)) {
                            $this->renderForm($login_form);
                        }

                        // Don't render the page if the user is not allowed access
                        if ($this->page_access) {
                             if (isset($this->form)) {
                                $this->renderForm($this->form);
                            }

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

        // Get default scripts and form scripts
        if ($this->render_scripts) echo getScripts();
        if (isset($login_form)) $this->printScripts($login_form);
        if ($this->page_access) $this->printScripts($this->form);

        ?>
        </html>
    <?php
    }
}
?>
