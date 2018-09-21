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
    protected $msg;             // Any error or success message thrown by this class

    public function __construct($form_ID, $table_name, $layout, $attr, $framework) {
        $this->form_ID = $form_ID;
        $this->table_name = $table_name;
        $this->db = new Mysql();
        $this->setPageTitle();
        $this->setFilterArray();
        $this->setUpdateArray();
        $this->registerPostActions();

        //unset($_SESSION[$this->form_ID]); // Debug purposes

        // FILL FORM
        // ------------------------------
        // If editing a form, then populate the fields with the current database values
        if (isset($_GET["edit"])) {
            $this->db->selectRows($this->table_name, $this->filter);
            // If could not find the object in the database with which to fill in the form
            if ($this->db->rowCount() == 0) {
                // By convention, the most specific identifier is the last GET variable
                $last_value = end(array_values($_GET));
                $this->storeErrorMsg("Could not find ".$last_value." in database");
            } else {
                $this->fillFormWithDbValues($this->db->recordsArray()[0]);
            }
        }

        // If the form has been posted (saved, deleted, etc) back to the server
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Security token is automatically added to each form.
            // Token is valid for 1800 seconds (30mn) without refreshing page
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
                        if (is_callable($function_name)) {
                            $this->$function_name();
                        } else {
                            $this->storeErrorMsg("Function '".$function_name."' undefined or not implemented yet");
                        }
                    }
                }
                // Validate form -> check if it has been filled out correctly
                $this->validator = Form::validate($this->form_ID);
                if ($this->validator->hasErrors()) {
                    $_SESSION['errors'][$this->form_ID] = $this->validator->getAllErrors();
                } else {
                    // Call any post actions that require validation
                    foreach ($this->post_actions["valid"] as $function_name => $should_call_function) {
                        if (is_callable($function_name)) {
                            $this->$function_name();
                        } else {
                            $this->storeErrorMsg("Function '".$function_name."' undefined or not implemented yet");
                        }
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

    // Deletes the form_ID from the database based on a filter (primary keys)
    // If you override this function you must not call the parent function, otherwise the redirect will not execute
    // the rest of your code
    protected function delete() {
        $this->db->deleteRows($this->table_name, $this->filter);
        if ($this->db->error()) {
            $this->storeDbMsg();
        } else {
            $success_message = urlencode(ucwords($this->form_ID) . " successfully deleted!");
            header("location: index.php?success_message=".$success_message);
            exit;
        }
    }

    // Creates the form_ID in the database based on an $update array ($column_name => $value)
    protected function create() {
        $this->db->insertRow($this->table_name, $this->update);
        $this->storeDbMsg("New " . $this->form_ID . " successfully created!");
    }

    // Updates the form_ID in the database identified by $filter with values from $update
    protected function update() {
        $this->db->updateRows($this->table_name, $this->update, $this->filter);
        $this->storeDbMsg(ucwords($this->form_ID) . " successfully updated!");
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
            if(isset($_POST[$column_name])) {
                $this->update[$column_name] = Mysql::SQLValue($_POST[$column_name]);
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
        $this->storeErrorMsg($calling_method_name. " function not implemented!");
    }

    // Stores a success or fail message to the user based on the results of a database query
    // If no fail message given then it will store the SQL error
    protected function storeDbMsg($success_msg = null, $fail_msg = null, $errors_only = false) {
        // If the database has thrown any errors
        if ($this->db->error()) {
            // If a fail message hasn't been set
            if ($fail_msg == null) {
                // Set the fail message to the given database error
                $fail_msg = $this->db->error() . '<br>' . $this->db->getLastSql();
            }
            $this->msg .= '<p class="alert alert-danger">'.$fail_msg.'</p>';
        } else if (!$errors_only) {
            if ($success_msg == null) {
                $this->msg .= '<p class="alert alert-success">Success!</p>';
            } else {
                $this->msg = '<p class="alert alert-success">' . $success_msg . '</p>';
            }
        }
    }

    protected function storeErrorMsg($error_msg) {
        $this->msg = '<p class="alert alert-danger">' . $error_msg . '</p>';
    }

    protected function storeSuccessMsg($success_msg) {
        $this->msg = '<p class="alert alert-success">' . $success_msg . '</p>';
    }

    // Primarily used by the Page_Renderer to print any messages
    public function getMsg() {
        return $this->msg;
    }
}
