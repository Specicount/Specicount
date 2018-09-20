<?php
/**
 * Created by IntelliJ IDEA.
 * User: Elliott
 * Date: 24/08/2018
 * Time: 7:30 PM
 */

namespace classes;


use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;
use function functions\getPrimaryKeys;
use function functions\getColumnNames;
use function functions\printDbErrors;
use function functions\printError;

//require_once $_SERVER["DOCUMENT_ROOT"]."/page-components/functions.php";

/**
 * Class Post_Form
 * @package classes
 *
 * On instantiation, this class will:
 *      - Fill in a form that you are editing with database values
 *      - Deal with forms posted back to the server by:
 *          - Validating them
 *          - Applying the appropriate post action to the database based on the button that was pressed on the form
 */
abstract class Post_Form extends Form {

    protected $page_title; // What gets displayed on the browser tab and on the navbar
    protected $table_name;
    protected $update, $filter;
    protected $post_actions;
    protected $db;

    //public abstract function setFormType(); //Set the $form_type variable to a string, e.g. 'project', 'core', 'sample', etc

    public function __construct($form_ID, $table_name, $layout, $attr, $framework) {
        $this->form_ID = $form_ID;
        $this->table_name = $table_name;
        $this->db = new Mysql();
        $this->setPageTitle();
        $this->setFilterArray();
        $this->setUpdateArray();
        $this->registerPostActions();

        //unset($_SESSION[$this->form_ID]); // Remove

        // FILL FORM
        // ------------------------------
        // If editing a form, then populate the fields with the current database values
        if (isset($_GET["edit"])) {
            $this->db->selectRows($this->table_name, $this->filter);
            // If could not find the object in the database with which to fill in the form
            if ($this->db->rowCount() == 0) {
                // By convention, the most specific identifier is the last GET variable
                $last_value = end(array_values($_GET));
                printError("Could not find ".$last_value." in database");
            } else {
                $this->fillFormWithDbValues($this->db->recordsArray()[0]);
            }
        }

        // If the form has been posted (saved, deleted, etc) back to the server
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            //Security token is automatically added to each form.
            //Token is valid for 1800 seconds (30mn) without refreshing page
            if (Form::testToken($this->form_ID) === true) {
                // PERMISSIONS CHECK
                // ------------------------------
                // If trying to interact with a page related to a project
                if (isset($_GET["project_id"])) {
                    $page = strtok(basename($_SERVER['HTTP_REFERER']), "?");
                    // If not just trying to create a new project (all users are allowed to do that)
                    if (!($page == "add_new_project.php" && !isset($_GET["edit"]))) {
                        ;
                        $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
                        $filter["username"] = Mysql::SQLValue($_SESSION["username"]);
                        $this->db->selectRows("user_project_access", $filter);
                        $user = $this->db->recordsArray()[0];
                        // If user is only a visitor
                        if ($user["access_level"] == "visitor") {
                            // Deny their changes and redirect them to home page
                            $error_message = urlencode("You do not have the correct permissions to perform those changes");
                            header("location: index.php?error_message=" . $error_message);
                            exit;
                        }

                    }
                }

                // POST ACTIONS
                // ------------------------------
                // Call any post actions that don't require validation (e.g. delete actions)
                foreach ($this->post_actions["no_valid"] as $function_name => $should_call_function) {
                    if ($should_call_function) {
                        $this->$function_name();
                    }
                }
                // Validate form -> check if it has been filled out correctly
                $validator = Form::validate($this->form_ID);
                if ($validator->hasErrors()) {
                    $_SESSION['errors'][$this->form_ID] = $validator->getAllErrors();
                } else {
                    // Call any post actions that require validation
                    foreach ($this->post_actions["valid"] as $function_name => $should_call_function) {
                        if ($should_call_function) {
                            $this->$function_name();
                        }
                    }
                }
            }
        }

        parent::__construct($form_ID, $layout, $attr, $framework);
    }

    // If editing a form, fill in the fields with the current database values
    protected function fillFormWithDbValues($record_array) {
        foreach($record_array as $column_name => $value) {
            $_SESSION[$this->form_ID][$column_name] = $value;
        }
    }

    // Deletes the form_ID from the database based on a filter (primary keys)
    // If you override this function you must not call the parent function, otherwise the redirect will not execute
    // the rest of your code
    protected function delete() {
        $this->db->deleteRows($this->table_name, $this->filter);
        if ($this->db->error()) {
            printDbErrors($this->db);
        } else {
            $success_message = urlencode(ucwords($this->form_ID) . " successfully deleted!");
            header("location: index.php?success_message=".$success_message);
            exit;
        }
    }

    // Creates the form_ID in the database based on an $update array ($column_name => $value)
    protected function create() {
        print_r("creating new core with ");
        print_r($this->update);
        $this->db->insertRow($this->table_name, $this->update);
        printDbErrors($this->db, "New " . $this->form_ID . " successfully created!");
    }

    // Updates the form_ID in the database identified by $filter with values from $update
    protected function update() {
        $this->db->updateRows($this->table_name, $this->update, $this->filter);
        printDbErrors($this->db, ucwords($this->form_ID) . " successfully updated!");
    }

    // Register a new post action that will call the function identified by the string $function_name when $bool = true
    // All post action functions should take in three arguments: $db, $update and $filter, regardless
    // of whether the function uses them or not since some functions need all three arguments (e.g. update)
    protected function registerPostAction($function_name, $bool, $validation_required=true) {
        if ($validation_required) {
            $this->post_actions["valid"][$function_name] = $bool;
        } else {
            $this->post_actions["no_valid"][$function_name] = $bool;
        }

    }


    // Boolean expressions constructed such that only one of them is ever true at a single time
    protected function registerPostActions() {
        $this->registerPostAction("create", isset($_POST["submit-btn"]) && $_POST["submit-btn"] == "save" && !isset($_GET["edit"]));
        $this->registerPostAction("update", isset($_POST["submit-btn"]) && $_POST["submit-btn"] == "save" &&  isset($_GET["edit"]));
        $this->registerPostAction("delete", isset($_POST["delete-btn"]) && $_POST["delete-btn"] == "delete", false);
    }

    protected function setUpdateArray() {
        // Get all the column names of the given table
        $column_names = getColumnNames($this->table_name);
        // Create an array which stores the new values to update for each column
        foreach ($column_names as $column_name) {
            if(isset($_POST[$column_name])) {
                $update[$column_name] = Mysql::SQLValue($_POST[$column_name]);
            } else if (isset($_GET[$column_name])) {
                $update[$column_name] = Mysql::SQLValue($_GET[$column_name]);
            }
        }

        $this->update = $update;
    }

    protected function setFilterArray() {
        // Create an array which stores the posted values of the primary keys to identify which row to update
        $primary_keys = getPrimaryKeys($this->table_name);
        foreach ($primary_keys as $pk) {
            if (isset($_GET[$pk])) {
                $filter[$pk] = Mysql::SQLValue($_GET[$pk]);
            }
        }
        $this->filter = $filter;
    }

    public function getFilterArray() {
        return $this->filter;
    }

    protected function setPageTitle() {
        $this->page_title = ucwords($this->form_ID) . " Form";
    }

    public function getPageTitle() {
        return $this->page_title;
    }

    public function getFormName() {
        return $this->form_ID;
    }

    public function getTableName() {
        return $this->table_name;
    }

    protected function raiseNotImplemented() {
        $calling_method_name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'];
        printError($calling_method_name. " function not implemented!");
    }
}
