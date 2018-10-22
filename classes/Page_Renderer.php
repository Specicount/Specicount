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

ini_set('session.cookie_httponly',1);
ini_set('session.cookie_secure',1);

use phpformbuilder\Form;
use classes\Post_Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

// Uncomment to view all errors
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


// The base website link (without parameters)
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

// Add required files
$current_dir = __DIR__;
include_once $current_dir.'/../phpformbuilder/Form.php';
require_once $current_dir.'/../classes/Post_Form.php';
require_once $current_dir.'/../phpformbuilder/database/Mysql.php';
require_once $current_dir.'/../phpformbuilder/database/db-connect.php';
require_once $current_dir.'/../page-components/functions.php';
require_once $current_dir.'/../page-components/components.php';

createMinFolders();

session_start();
// Test if the user is logged in

class Page_Renderer {

    private $page_title;

    /*
     * @var Form
     */
    private $form;

    //Variables that will store content to render on the page
    //Will only be rendered if not null
    private $html_string, $render_header, $render_navbar, $render_sidebar, $render_scripts, $render_div_container;

    // Variables that determine page restrictions (see function setPageRestrictions)
    private $require_login, $require_project, $require_core, $require_sample, $require_specimen;

    // Determines whether the page will be rendered (see function setPageAccess)
    private $page_access = true;

    public function __construct() {
        $this->render_header
            = $this->render_navbar
            = $this->render_scripts
            = $this->render_sidebar
            = $this->render_div_container
            = true;
        $this->setPageRestrictions();
    }

    /**
     * Set the title of the page
     * @param $title string title of page
     */
    public function setPageTitle($title) {
        $this->page_title = $title;
    }

    /**
     * Disable the stylesheets and other header information
     */
    public function disableHeader() {
        $this->render_header = false;
    }

    /**
     * Disable the navbar
     */
    public function disableNavbar() {
        $this->render_navbar = false;
    }

    public function disableDivContainer() {
        $this->render_div_container = false;
    }

    /**
     * Disable the sidebar
     */
    public function disableSidebar() {
        $this->render_sidebar = false;
    }

    /**
     * Disable the JS scripts to be placed on the page
     */
    public function disableScripts() {
        $this->render_scripts = false;
    }

    /**
     * Set the form(s) to render
     * @param $form Form|array
     */
    public function setForm($form) {
        $this->form = $form;
    }

    /**
     * Set the inner html of the page
     * @param $html_string string the HTML to render on the page
     */
    public function setInnerHTML($html_string) {
        $this->html_string = $html_string;
    }

    /**
     * Print CSS files of form(s)
     * @param $form Form|array
     */
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

    /**
     * Print script files of form(s)
     * @param $form Form|array
     */
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

    /**
     * Render form(s)
     * @param $form Form|array
     */
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
     * Set restrictions for page
     * @param bool $require_login       true if the page requires a user to be logged in
     * @param bool $require_project     true if the page requires a valid project_id GET variable
     * @param bool $require_core        true if the page requires a valid core_id GET variable
     * @param bool $require_sample      true if the page requires a valid sample_id GET variable
     */
    public function setPageRestrictions($require_login = false, $require_project = false, $require_core = false, $require_sample = false, $require_specimen = false) {
        $this->require_login = $require_login;
        $this->require_project = $require_project;
        $this->require_core = $require_core;
        $this->require_sample = $require_sample;
        $this->require_specimen = $require_specimen;
    }

    /**
     * Determines whether the page will be rendered based on class variables set by setPageRestrictions
     */
    private function setPageAccess() {
        // ------------- user logged in -------------
        if ($this->require_login && !isset($_SESSION["email"])) {
            $this->page_access = false;
            storeErrorMsg("You must be logged in to view this page");
            return;
        }

        // ------------- database object exists -------------
        $project_id = $core_id = $sample_id = $specimen_id = null;
        $required_array["project_id"] = $this->require_project;
        $required_array["core_id"] = $this->require_core;
        $required_array["sample_id"] = $this->require_sample;
        $required_array["specimen_id"] = $this->require_specimen;
        foreach ($required_array as $object_id => $object_is_required) {
            if ($object_is_required) {
                if (isset($_GET[$object_id])) {
                    $$object_id = Mysql::SqlValue($_GET[$object_id]);
                } else {
                    $this->page_access = false;
                    storeErrorMsg("You must specify a $object_id in the URL");
                    return;
                }
            }
        }

        $db = new Mysql();
        // If trying to access a specimen
        if ($this->require_specimen && !$db->querySingleValue("SELECT specimen_id FROM specimens WHERE project_id=$project_id AND specimen_id=$specimen_id")) {
            $this->page_access = false;
            storeErrorMsg("Specimen $specimen_id not found in database");
            return;
        }
        // If trying to access a sample
        else if ($this->require_sample && !$db->querySingleValue("SELECT sample_id FROM samples WHERE project_id=$project_id AND core_id=$core_id AND sample_id=$sample_id")) {
            $this->page_access = false;
            storeErrorMsg("Sample $sample_id not found in database");
            return;
        }
        // If trying to access a core
        else if ($this->require_core && !$db->querySingleValue("SELECT core_id FROM cores WHERE project_id=$project_id AND core_id=$core_id")){
            $this->page_access = false;
            storeErrorMsg("Core $core_id not found in database");
            return;
        }
        // If trying to access a project
        else if ($this->require_project && !$db->querySingleValue("SELECT project_id FROM projects WHERE project_id=$project_id")){
            $this->page_access = false;
            storeErrorMsg("Project $project_id not found in database");
            return;
        }

        // ------------- user has correct access level -------------
        // If trying to access any page connected to a project (and the database object exists)
        if ($this->require_project) {
            $my_access_level = getAccessLevel();
            // If the user does not have access to that project (since objects are connected to a project)
            if (!$my_access_level) {
                $this->page_access = false;
                storeErrorMsg("You do not have access to project $project_id");
                return;
            }
        }
    }

    /**
     * Create the page for displaying
     */
    public function renderPage() {
        // Use the form's default page title if a page title hasn't been explicitly set
        if (empty($this->page_title)) {
            if (isset($this->form) && !is_array($this->form)) {
                $this->page_title = $this->form->getPageTitle();
            } else {
                $this->page_title = "UNTITLED";
            }
        }

        // If login button was pressed on login modal

        if (!isset($_SESSION['email'])) {
            if (isset($_POST['do-login'])) {
                if (!$_POST['email']) {
                    storeErrorMsg('Please enter an email address');
                } else if (!$_POST['password']) {
                    storeErrorMsg('Please enter a password');
                } else {
                    $db = new Mysql();
                    $db->selectRows('users', array('email' => Mysql::SQLValue($_POST['email'])));
                    $user = $db->recordsArray()[0];
                    if (password_verify($_POST['password'], $user["password"])) {
                        $_SESSION['email'] = $_POST['email'];
                        $current_script = basename(getTopMostScript(), ".php");
                        if ($current_script == "register" || $current_script == "password_reset") {
                            header("location: index.php");
                        }
                        echo '<script>window.location = window.location.href;</script>';
                    } else {
                        storeErrorMsg('Incorrect username or password');
                    }
                }
            }

            // If forgot password button was pressed on password reset modal
            if (isset($_POST['do-forgot-password'])) {
                global $actual_link;
                sendForgotPasswordEmail($actual_link);
            }
            // If not logged in
            $login_form = array(getLoginModal(), getForgotPasswordModal());

            // Sample Side Bar (shown if sample is selected)
            $this->render_sidebar = false;
        }

        $this->setPageAccess();

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

        if ($this->render_div_container) {
            ?>
            <div class="d-flex">
                <?php
                // Render the sidebar
                if ($this->render_sidebar && $this->page_access) {
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
                                echo end($messages["success"]);
//                            foreach ($messages["success"] as $success_msg) {
//                                echo $success_msg;
//                            }
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
        }

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
