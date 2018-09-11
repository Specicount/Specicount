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

require_once $_SERVER["DOCUMENT_ROOT"]."/page-components/functions.php";

abstract class Abstract_Form {

    private $form_type;  // E.g. project, core, sample etc
    private $form_name;  // A string that uniquely identifies the form for phpformbuilder
    private $page_title; // What gets displayed on the browser tab and on the navbar
    private $table_name;

    public abstract function getFormType(); //Should return a string, e.g. 'project', 'core', 'sample', etc

    public function __construct() {
        $this->form_type = strtolower($this->getFormType()); //strtolower just in case of bad data
        $this->table_name = $this->form_type . 's';

        if ($_GET["edit"]) {
            $id = end($_GET); // Last element of array is always the most specific id
            $this->form_name = 'add-new-'.$this->form_type.'-edit';
            $this->page_title = "Edit ".ucwords($this->form_type).' '.$id;
        } else {
            $this->form_name = 'add-new-'.$this->form_type;
            $this->page_title = 'Add New '.ucwords($this->form_type);
        }

        $db = new Mysql();

        $filter = $this->getFilterArray();

        // If the form has been posted (saved, deleted, etc) back to the server
        if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($this->form_name) === true) {
            // If the delete button was pressed
            if ($_POST["submit-btn"] == "delete") {
                // Call the form-specific delete function
                $this->delete($db, $filter);
                printDbErrors($db, ucwords($this->form_type).' deleted successfully!',null, true);
            } else {
                // Update or insert new rows into db
                $validator = Form::validate($this->form_name);
                if ($validator->hasErrors()) {
                    $_SESSION['errors'][$this->form_name] = $validator->getAllErrors();
                } else {

                    // If posted form has been filled out correctly

                    $update = $this->getUpdateArray();
                    //print_r($update);
                    if ($_GET["edit"]) {
                        $this->update($db, $update, $filter);
                    } else {
                        $this->create($db, $update);
                    }

                    printDbErrors($db, "Database updated successfully !");
                }
            }
        }

        // If editing, then fill in the fields with the current database values
        if ($_GET["edit"]) {
            unset($_SESSION[$this->form_name]);
            $db->selectRows($this->table_name, $filter);
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
        $db->deleteRows($this->getTableName(), $filter);
    }

    // Creates the form_type in the database based on an $update array ($column_name => $value)
    protected function create($db, $update) {
        $db->insertRow($this->getTableName(), $update);
    }

    // Updates the form_type in the database identified by $filter with values from $update
    protected function update($db, $update, $filter) {
        $db->updateRows($this->getTableName(), $update, $filter);
    }

    protected function getUpdateArray() {
        // Get all the column names of the given table
        $column_names = getColumnNames($this->table_name);
        // Create an array which stores the new values to update for each column
        foreach ($column_names as $column_name) {
            if($_POST[$column_name]) {
                $update[$column_name] = Mysql::SQLValue($_POST[$column_name]);
            } else if ($_GET[$column_name]) {
                $update[$column_name] = Mysql::SQLValue($_GET[$column_name]);
            }
        }

        return $update;
    }

    protected function getFilterArray() {
        // Create an array which stores the posted values of the primary keys to identify which row to update
        $primary_keys = getPrimaryKeys($this->table_name);
        foreach ($primary_keys as $pk) {
            $filter[$pk] = Mysql::SQLValue($_GET[$pk]);
        }
        return $filter;
    }

    public function getPageTitle() {
        return $this->page_title;
    }

    public function getFormName() {
        return $this->form_name;
    }

    public function getTableName() {
        return $this->table_name;
    }
}
