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
use classes\Post_Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;
use function functions\getTopMostScript;
use function functions\getAccessLevel;
use function getLoginForm;
use function functions\storeErrorMsg;


// Add required files
$current_dir = __DIR__;
include_once $current_dir.'/../phpformbuilder/Form.php';
require_once $current_dir.'/../classes/Post_Form.php';
require_once $current_dir.'/../phpformbuilder/database/Mysql.php';
require_once $current_dir.'/../phpformbuilder/database/db-connect.php';
require_once $current_dir.'/../page-components/functions.php';
require_once $current_dir.'/../login_modal.php';

session_start();
// Test if the user is logged in

class Page_Renderer {

    private $page_title;

    //Whether the page requires a user to be logged in to view its contents
    private $require_login;

    //Variables that will store content to render on the page
    //Will only be rendered if not null
    private $form, $login_form, $html_string, $render_header, $render_navbar, $render_sidebar, $render_scripts;

    // Determines whether the page will be rendered
    private $page_access = true;

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

    public function setPageAccess($project = false, $core = false, $sample = false) {
        // If user is logged in
        if (isset($_SESSION['email'])) {
            $project_id = Mysql::SqlValue($_GET["project_id"]);
            $core_id = Mysql::SqlValue($_GET["core_id"]);
            $sample_id = Mysql::SqlValue($_GET["sample_id"]);

            $db = new Mysql();

            // If trying to access a sample
            if ($sample && !$db->querySingleValue("SELECT sample_id FROM samples WHERE project_id=$project_id AND core_id=$core_id AND sample_id=$sample_id")) {
                storeErrorMsg("Sample not found in database");
                $this->page_access = false;
            }
            // If trying to access a core
            else if ($core && !$db->querySingleValue("SELECT core_id FROM cores WHERE project_id=$project_id AND core_id=$core_id")){
                storeErrorMsg("Core not found in database");
                $this->page_access = false;
            }
            // If trying to access a project
            else if ($project && !$db->querySingleValue("SELECT project_id FROM projects WHERE project_id=$project_id")){
                storeErrorMsg("Project not found in database");
                $this->page_access = false;
            }

            // If trying to access any page connected to a project and the page's database equivalent exists
            if ($project && $this->page_access) {
                $my_access_level = getAccessLevel();
                // If the user does not have access to that project
                if (!$my_access_level) {
                    $this->page_access = false;
                }
            }
        }
    }

    // Create the page
    public function renderPage() {

        // The following code can't be in setPageAccess because you can't rely on that function always being called
        // If user is not logged in
        if (!isset($_SESSION['email'])) {
            // Create login form
            $this->login_form = getLoginForm();
            // If this page requires a user to be logged in
            if ($this->require_login) {
                $this->page_access = false;
            }
        }

        // Use the form's default page title if a page title hasn't been explicitly set
        if (!isset($this->page_title)) {
            if (isset($this->form)) {
                $this->page_title = $this->form->getPageTitle();
            } else {
                $this->page_title = "UNTITLED";
            }
        }

        // Get current folder
        $current_dir = __DIR__;

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title><?=$this->page_title?></title>
            <?php
            if ($this->render_header) {
                $render_side = $this->render_sidebar;
                require_once $current_dir.'/../page-components/header.php';
            }

            //Check whether $form has been initialised so we can add the relevant css files
            if (isset($this->login_form)) $this->printCSS($this->login_form);
            if ($this->page_access) $this->printCSS($this->form);

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
                        // $sent_message refers to any message thrown by PHPFormBuilder
                        global $sent_message;
                        if (isset($sent_message)) {
                            echo $sent_message;
                        }

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
                        if (isset($this->login_form)) {
                            $this->renderForm($this->login_form);
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
                        } else {
                            echo '<p class="alert alert-danger">You do not have access to this page</p>';
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

        if (isset($this->login_form)) $this->printScripts($this->login_form);
        if ($this->page_access) $this->printScripts($this->form);

        ?>
        </html>
    <?php
    }
}
?>
