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

    public abstract function setFormType(); //Set the $form_type variable to a string, e.g. 'project', 'core', 'sample', etc
    public abstract function setSqlTableName(); //Set the $table_name variable to a string, e.g. 'projects', 'cores', 'samples', etc

    public function __construct() {
        $this->setFormType();
        $this->setFormName();
        $this->setPageTitle();
        $this->setSqlTableName();
        $this->setFilterArray();

        //unset($_SESSION[$this->form_name]); // Remove
        $db = new Mysql();

        // If the form has been posted (saved, deleted, etc) back to the server
        if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($this->form_name) === true) {

            // PERMISSIONS CHECK
            // -----------------
            // If trying to interact with a page related to a project
            if (isset($_GET["project_id"])) {
                $page = strtok(basename($_SERVER['HTTP_REFERER']), "?");
                // If not just trying to create a new project
                if (!($page == "add_new_project.php" && !isset($_GET["edit"]))) {;
                    $filter["project_id"] = Mysql::SQLValue($_GET["project_id"]);
                    $filter["username"] = Mysql::SQLValue($_SESSION["username"]);
                    $db->selectRows("user_project_access", $filter);
                    $user = $db->recordsArray()[0];
                    // If user is only a visitor
                    if ($user["access_level"] == "visitor") {
                        // Deny their changes and redirect them to home page
                        header("location: index.php?error=invalid_permissions");
                        exit;
                    }

                }

            }
            // -----------------


            // If the delete button was pressed
            if ($_POST["submit-btn"] == "delete") {
                // Call the form-specific delete function
                $this->delete($db, $this->filter);
            } else {

                // If posted form has been filled out correctly
                $validator = Form::validate($this->form_name);
                if ($validator->hasErrors()) {
                    $_SESSION['errors'][$this->form_name] = $validator->getAllErrors();
                } else {

                    $this->setUpdateArray();
                    //print_r($update);
                    if ($_GET["edit"]) {
                        $this->update($db, $this->update, $this->filter);
                    } else {
                        $this->create($db, $this->update);
                    }
                }
            }
        }

        // If editing, then fill in the fields with the current database values
        if ($_GET["edit"]) {
            $db->selectRows($this->table_name, $this->filter);
            printDbErrors($db, null, null, false, true);
            $this->fillFormWithDbValues($db->recordsArray()[0]);
        }
    }

    // If editing a form, fill in the fields with the current database values
    protected function fillFormWithDbValues($record_array) {
        foreach($record_array as $column_name => $value) {
            $_SESSION[$this->form_name][$column_name] = $value;
        }
    }

    // Deletes the form_type from the database based on a filter (primary keys)
    protected function delete($db, $filter) {
        $db->deleteRows($this->table_name, $filter);
        printDbErrors($db, ucwords($this->form_type)." successfully deleted!",null, true);
    }

    // Creates the form_type in the database based on an $update array ($column_name => $value)
    protected function create($db, $update) {
        $db->insertRow($this->table_name, $update);
        printDbErrors($db, "New ".$this->form_type." successfully created!");
    }

    // Updates the form_type in the database identified by $filter with values from $update
    protected function update($db, $update, $filter) {
        $db->updateRows($this->table_name, $update, $filter);
        printDbErrors($db, ucwords($this->form_type)." successfully updated!");
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
