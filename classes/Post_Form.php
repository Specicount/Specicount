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

    protected $page_title;      // An optional title provided by this form that the page renderer can use if it wants to
    protected $table_name;      // The SQL table name that the post actions will interact with
    protected $update;          // An array of column_name => value which can be used to update a row in the database
    protected $filter;          // An array of primary_key => value which can be used to select a row in the database
    protected $post_actions;    // An associative array of function_name => boolean where true means function_name will be executed
    protected $db, $validator;  // PHPFormBuilder variables needed to interact with the database and validate forms
    protected $required_get_variables; // The GET variables that must be set for the form to interact with the database properly
    // A user must have one of the access levels from this array if they want to post the form (only affects forms related to a project, might want to create a new subclass for this)
    protected $post_required_access_levels;


    public function __construct($form_ID, $table_name, $layout, $attr, $framework) {
        $this->form_ID = $form_ID;
        $this->table_name = $table_name;
        $this->db = new Mysql();
        $this->setPageTitle();
        $this->setFilterArray();
        $this->setUpdateArray();
        $this->setRequiredAccessLevelsForPost();
        $this->registerPostActions();


//        unset($_SESSION[$this->form_ID]); // Debug purposes
//        $_SESSION["email"] = "alex@niven.com";
//        $_SESSION["email"] = "elliott.wagener@hotmail.com";
//        $_SESSION["email"] = "matthew.knill@hotmail.com";
//        $_SESSION["email"] = "gay@fools.com";


//        print_r($_SESSION["email"]." is ".getAccessLevel());

        // FILL FORM
        // ------------------------------
        // If editing a form, then populate the fields with the current database values
        if (isset($_GET["edit"])) {
            $this->db->selectRows($this->table_name, $this->filter);
            // If could not find the object in the database with which to fill in the form
            if ($this->db->rowCount() == 0) {
                // By convention, the most specific identifier is the last GET variable
                $last_value = end(array_values($_GET));
                storeErrorMsg("Could not find ".$last_value." in database");
            } else {
                $this->fillFormWithDbValues($this->db->recordsArray()[0]);
            }
        }

        // If the form has been posted (saved, deleted, etc) back to the server
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Security token is automatically added to each form.
            // Token is valid for 1800 seconds (30mn) without refreshing page
            if (Form::testToken($this->form_ID) === true) {
                $execute_post_actions = true;
                // PERMISSIONS CHECK
                // ------------------------------
                // If trying to interact with a page related to a project
                if (isset($_GET["project_id"])) {
                    $my_access_level = getAccessLevel();
                    if (!in_array($my_access_level, $this->post_required_access_levels)) {
                        storeErrorMsg("You do not have the correct permissions to perform those changes");
                        $execute_post_actions = false;
                    }
                }

                // POST ACTIONS
                // ------------------------------
                if ($execute_post_actions) {
                    // Call any post actions that don't require validation (e.g. delete actions)
                    $this->executePostActions($this->post_actions["no_valid"]);
                    // Create validator and auto-validate required fields
                    $this->validator = Form::validate($this->form_ID);
                    // Make any other validations such as email, password matching, etc
                    $validation_succeeded = $this->additionalValidation();
                    if (!$validation_succeeded || $this->validator->hasErrors()) {
                        // TODO: Should probably show the user the error message at some point
                        // Yeah well if we use the validator
                        $_SESSION['errors'][$this->form_ID] = $this->validator->getAllErrors();
                    } else {
                        // Call any post actions that require validation (e.g. register user)
                        $this->executePostActions($this->post_actions["valid"]);
                    }
                }
            }
        }

        // This has to be done after Form::testToken validation because
        // creating a new Form updates the SESSION token and then it doesn't match with the POST token
        parent::__construct($form_ID, $layout, $attr, $framework);
    }

    // If editing a form, fill in the fields with the current database values
    protected function fillFormWithDbValues($record_array) {
        foreach($record_array as $column_name => $value) {
            $_SESSION[$this->form_ID][$column_name] = $value;
        }
    }

    // This function should use the $validator class variable to validate the form
    // Must return true if validation succeeded
    // It can also return false if some other method of validation failed
    protected function additionalValidation() {
        return true;
    }

    private function executePostActions($post_actions) {
        foreach ($post_actions as $function_name => $should_call_function) {
            if ($should_call_function) {
                if (method_exists($this,$function_name)) {
                    $this->$function_name();
                } else {
                    storeErrorMsg("Function '".$function_name."' undefined or not implemented yet");
                }
            }
        }
    }

    // Deletes the form_ID from the database based on a filter (primary keys)
    // If you override this function you must not call the parent function, otherwise the redirect will not execute
    // the rest of your code
    protected function delete() {
        $this->db->deleteRows($this->table_name, $this->filter);
        storeDbMsg($this->db);
    }

    // Creates the form_ID in the database based on an $update array ($column_name => $value)
    protected function create() {
        $this->db->insertRow($this->table_name, $this->update);
        storeDbMsg($this->db,"New " . $this->form_ID . " successfully created!");
    }

    // Updates the form_ID in the database identified by $filter with values from $update
    protected function update() {
        $this->db->updateRows($this->table_name, $this->update, $this->filter);
        storeDbMsg($this->db,ucwords($this->form_ID) . " successfully updated!");
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


    // This function determines which other functions should be executed (when the form is posted) and under what specific conditions
    // Boolean expressions should be constructed such that only one of them is ever true at a single time
    protected function registerPostActions() {
        $this->registerPostAction("create", isset($_POST["submit-btn"]) && $_POST["submit-btn"] == "save" && !isset($_GET["edit"]));
        $this->registerPostAction("update", isset($_POST["submit-btn"]) && $_POST["submit-btn"] == "save" &&  isset($_GET["edit"]));
        $this->registerPostAction("delete", isset($_POST["delete-btn"]) && $_POST["delete-btn"] == "delete", false);
    }

    // Sets the $update array variable based on the values in the posted form
    protected function setUpdateArray() {
        $column_names = getColumnNames($this->table_name);
        foreach ($column_names as $column_name) {
            if (isset($_POST[$column_name])) {
                $this->update[$column_name] = Mysql::SQLValue($_POST[$column_name]);
            } else if (isset($_GET[$column_name])) {
                $this->update[$column_name] = Mysql::SQLValue($_GET[$column_name]);
            }
        }
    }

    // Sets the $filter array variable based on the value of the primary keys in the GET variables.
    protected function setFilterArray() {
        $primary_keys = getPrimaryKeys($this->table_name);
        foreach ($primary_keys as $pk) {
            if (isset($_GET[$pk])) {
                $this->filter[$pk] = Mysql::SQLValue($_GET[$pk]);
            }
        }
    }

    public function getFilterArray() {
        return $this->filter;
    }

    protected function setRequiredGetVariables() {
        return getPrimaryKeys($this->table_name);
    }

    // A user must have one of these access levels if they are to post this form
    protected function setRequiredAccessLevelsForPost() {
        $this->post_required_access_levels = array("owner","admin","collaborator");
    }

    public function getRequiredAccessLevelsForPost() {
        return $this->post_required_access_levels;
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


    // Call this function in a post action if you haven't implemented that post action yet
    protected function raiseNotImplemented() {
        $calling_method_name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'];
        storeErrorMsg($calling_method_name. " function not implemented!");
    }
}
