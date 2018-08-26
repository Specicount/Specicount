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

function get_topmost_script() {
    $backtrace = debug_backtrace(
        defined("DEBUG_BACKTRACE_IGNORE_ARGS")
            ? DEBUG_BACKTRACE_IGNORE_ARGS
            : FALSE);
    $top_frame = array_pop($backtrace);
    return $top_frame['file'];
}

$current_dir = __DIR__;
include_once $current_dir.'/../phpformbuilder/Form.php';
require_once $current_dir.'/../phpformbuilder/database/db-connect.php';
require_once $current_dir.'/../phpformbuilder/database/Mysql.php';

session_start();
print_r($_SESSION["auth_user"]);
if(!isset($_SESSION["auth_user"])){
    if (basename(get_topmost_script(), ".php") != "login.php") {
        //$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        header("location: login.php");
        exit;
    }
}

class Page_Renderer {

    private $page_title = "UNTITLED";

    //Variables that will store content to render on the page
    //Will only be rendered if not null
    private $form, $html_string, $render_header, $render_navbar, $render_sidebar, $render_scripts;

    // Archived: $php_filename

    //Boolean variables that determine what page_components to render
    //Will be rendered by default

    public function __construct() {
        $this->render_header = $this->render_navbar = $this->render_sidebar = $this->render_scripts = true;
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

    /*public function setPHPFile($php_filename) {
        $this->php_filename = $php_filename;
    }*/

    public function setInnerHTML($html_string) {
        $this->html_string = $html_string;
    }

    public function renderPage() {
        $current_dir = __DIR__;
    ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title><?= $this->page_title ?></title>
            <?php
            if ($this->render_header) {
                require_once $current_dir.'/../page-components/header.php';
            }

            //Check whether $form has been initialised so we can add the relevant css files
            if ($this->form) {
                //Since form could be an array that holds multiple form objects
                if (is_array($this->form)) {
                    foreach ($this->form as $f) {
                        $f->printIncludes('css');
                    }
                //Otherwise it's just a single form object
                } else {
                    $this->form->printIncludes('css');
                }
            }

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

                        if (isset($sent_message)) {
                            echo $sent_message;
                        } elseif (isset($msg)) {
                            echo $msg;
                        }

                        //Check whether $form has been initialised so we can render
                        if ($this->form) {
                            //Since form could be an array that holds multiple form objects
                            if (is_array($this->form)) {
                                foreach ($this->form as $f) {
                                    $f->render();
                                }
                                //Otherwise it's just a single form object
                            } else {
                                $this->form->render();
                            }
                        }

                        //Check whether $php_filename has been initialised so we can render
                        /*if ($this->php_filename) {
                            require_once $this->php_filename;
                        }*/

                        //Check whether $html_string has been initialised so we can render
                        if ($this->html_string) {
                            echo $this->html_string;
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

        //Check whether $form has been initialised so we can add the relevant js files
        if ($this->form) {
            //Since form could be an array that holds multiple form objects
            if (is_array($this->form)) {
                foreach ($this->form as $f) {
                    $f->printIncludes('js');
                    $f->printJsCode();
                }
                //Otherwise it's just a single form object
            } else {
                $this->form->printIncludes('js');
                $this->form->printJsCode();
            }
        }
        ?>
        </html>
    <?php
    }
}
?>
