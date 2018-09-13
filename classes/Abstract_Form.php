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

abstract class Abstract_Form {

    protected $form_type;  // E.g. project, core, sample etc
    protected $form_name;  // A string that uniquely identifies the form for phpformbuilder
    protected $page_title; // What gets displayed on the browser tab and on the navbar
    protected $table_name;
    protected $update, $filter;
    protected $post_actions;
    protected $db;

    public abstract function setFormType(); //Set the $form_type variable to a string, e.g. 'project', 'core', 'sample', etc
    public abstract function setSqlTableName(); //Set the $table_name variable to a string, e.g. 'projects', 'cores', 'samples', etc

    public function __construct() {
        $this->setFormType();
        $this->setFormName();
        $this->setPageTitle();
        $this->setSqlTableName();
        $this->setFilterArray();
        $this->setUpdateArray();
        $this->db = new Mysql();

        // Boolean expressions constructed such that only one of them is ever called at a time
        $this->registerPostAction("create", isset($_POST["submit-btn"]) && $_POST["submit-btn"] == "save" && !isset($_GET["edit"]));
        $this->registerPostAction("update", isset($_POST["submit-btn"]) && $_POST["submit-btn"] == "save" &&  isset($_GET["edit"]));
        $this->registerPostAction("delete", isset($_POST["delete-btn"]) && $_POST["delete-btn"] == "delete");


        //unset($_SESSION[$this->form_name]); // Remove

        // If the form has been posted (saved, deleted, etc) back to the server
        if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($this->form_name) === true) {

            // PERMISSIONS CHECK
            // -----------------
            // If trying to interact with a page related to a project
            if (isset($_GET["project_id"])) {
                $page = strtok(basename($_SERVER['HTTP_REFERER']), "?");
                // If not just trying to create a new project (all users are allowed to do that)
                if (!($page == "add_new_project.php" && !isset($_GET["edit"]))) {;
                    $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
                    $filter["username"] = Mysql::SQLValue($_SESSION["username"]);
                    $this->db->selectRows("user_project_access", $filter);
                    $user = $this->db->recordsArray()[0];
                    // If user is only a visitor
                    if ($user["access_level"] == "visitor") {
                        // Deny their changes and redirect them to home page
                        $error_message = urlencode("You do not have the correct permissions to perform those changes");
                        header("location: index.php?error=".$error_message);
                        exit;
                    }

                }
            }
            // -----------------

            // If posted form has been filled out correctly
            $validator = Form::validate($this->form_name);
            if ($validator->hasErrors()) {
                $_SESSION['errors'][$this->form_name] = $validator->getAllErrors();
            } else {
                // Call any functions that should be called
                foreach ($this->post_actions as $function_name => $should_call_function) {

                    if ($should_call_function) {
                        $this->$function_name();
                    }
                }
            }
        }

        // If editing, then fill in the fields with the current database values
        if ($_GET["edit"]) {
            $this->db->selectRows($this->table_name, $this->filter);
            printDbErrors($this->db, null, null, true);
            $this->fillFormWithDbValues($this->db->recordsArray()[0]);
        }
    }

    // If editing a form, fill in the fields with the current database values
    protected function fillFormWithDbValues($record_array) {
        foreach($record_array as $column_name => $value) {
            $_SESSION[$this->form_name][$column_name] = $value;
        }
    }

    // Deletes the form_type from the database based on a filter (primary keys)
    protected function delete() {
        $this->db->deleteRows($this->table_name, $this->filter);
        if ($this->db->error()) {
            printDbErrors($this->db);
        } else {
            $success_message = urlencode(ucwords($this->form_type) . " successfully deleted!");
            header("location: index.php?success=".$success_message);
            exit;
        }


    }

    // Creates the form_type in the database based on an $update array ($column_name => $value)
    protected function create() {
        $this->db->insertRow($this->table_name, $this->update);
        printDbErrors($this->db, "New " . $this->form_type . " successfully created!");
    }

    // Updates the form_type in the database identified by $filter with values from $update
    protected function update() {
        $this->db->updateRows($this->table_name, $this->update, $this->filter);
        printDbErrors($this->db, ucwords($this->form_type) . " successfully updated!");
    }

    // Register a new post action that will call the function identified by the string $function_name when $bool = true
    // All post action functions should take in three arguments: $db, $update and $filter, regardless
    // of whether the function uses them or not since some functions need all three arguments (e.g. update)
    protected function registerPostAction($function_name, $bool) {
        $this->post_actions[$function_name] = $bool;
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
            $filter[$pk] = Mysql::SQLValue($_GET[$pk]);
        }
        $this->filter = $filter;
    }

    protected function setPageTitle() {
        $this->page_title = ucwords($this->form_type) . " Form";
    }

    public function getPageTitle() {
        return $this->page_title;
    }

    protected function setFormName() {
        $this->form_name = $this->form_type;
    }

    public function getFormName() {
        return $this->form_name;
    }

    public function getTableName() {
        return $this->table_name;
    }

    protected function raiseNotImplemented() {
        $calling_method_name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'];
        printError($calling_method_name. " function not implemented!");
    }
}
