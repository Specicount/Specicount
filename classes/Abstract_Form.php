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

require_once $_SERVER["DOCUMENT_ROOT"]."/page-components/functions.php";

function debug($x) {
    print_r($x);
    echo '<br>';
}

abstract class Abstract_Form {

    private $form_type;  // E.g. project, core, sample etc
    private $form_name;  // A string that uniquely identifies the form for phpformbuilder
    private $page_title; // What gets displayed on the browser tab and on the navbar
    private $table_name;

    public abstract function getFormType(); //Should return a string, e.g. 'project', 'core', 'sample', etc
    public abstract function getTableName(); //Should return a string, e.g. 'projects', 'cores', 'samples', etc
    public abstract function delete($db, $filter);
    public abstract function submit($db, $update);
    public abstract function update($db, $update, $filter);



    public function __construct() {
        $this->form_type = strtolower($this->getFormType()); //strtolower just in case of bad data
        $this->table_name = $this->getTableName();

        if ($_GET["edit"]) {
            $id = end($_GET); // Last element of array is always the most specific id
            $this->form_name = 'add-new-'.$this->form_type.'-edit';
            $this->page_title = "Edit ".ucwords($this->form_type).' '.$id;
        } else {
            $this->form_name = 'add-new-'.$this->form_type;
            $this->page_title = 'Add New '.ucwords($this->form_type);
        }

        $db = new Mysql();

        // Create an array which stores the posted values of the primary keys to identify which row to update
        $primary_keys = getPrimaryKeys($this->table_name);
        foreach ($primary_keys as $pk) {
            $filter[$pk] = Mysql::SQLValue($_GET[$pk]);
        }
        //print_r($filter);

        // If the form has been posted (saved, deleted, etc) back to the server
        if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($this->form_name) === true) {
            // If the delete button was pressed
            if ($_POST["submit-btn"] == "delete") {
                // Call the form-specific delete function
                $this->delete($db, $filter);
                $this->printDbErrors($db, ucwords($this->form_type).' deleted successfully!',null);
            } else {
                // Update or insert new rows into db
                $validator = Form::validate($this->form_name);

                if ($validator->hasErrors()) {
                    $_SESSION['errors'][$this->form_name] = $validator->getAllErrors();
                } else {
                    // If posted form has been filled out correctly

                    $update = $this->getUpdateArray();
                    if ($_GET["edit"]) {
//                        print_r($filter);
//                        echo "<br>";
//                        print_r($update);
                        $this->update($db, $update, $filter);
                    } else {
                        $this->submit($db, $update);
                    }

                    $this->printDbErrors($db, "Database updated successfully !");
                }
            }
        }

        // If editing, then fill in the fields with the current database values
        if ($_GET["edit"]) {
            //$primary_keys = array_values($db->recordsArray()[0]['column_name']);
            unset($_SESSION[$this->form_name]);
            $db->selectRows($this->table_name, $filter);
            $this->fillFormWithDbValues($db->recordsArray()[0]);

        }
    }

    // Prints any database errors to the user
    // Usually executed after any calls to the database
    // Optional redirect to index.php on db success
    public function printDbErrors($db, $success_msg="Success!", $fail_msg=null, $redirect=false) {
        global $msg; // This variable is printed in the Page_Renderer class
        // If the database has thrown any errors
        if ($db->error()) {
            // If a fail message hasn't been set
            if ($fail_msg == null) {
                // Set the fail message to the given database error
                $fail_msg = $db->error() . '<br>' . $db->getLastSql();
            }
            $msg .= '<p class="alert alert-danger">'.$fail_msg.'</p>';
        } else {
            $msg = '<p class="alert alert-success">'.$success_msg.'</p>';
            if ($redirect) {
                header("Location: index.php");
            }
        }
    }

    public function getUpdateArray() {
        // Get all the column names of the given table
        $column_names = getColumnNames($this->table_name);
        debug($column_names);
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

    public function fillFormWithDbValues($record_array) {
        foreach($record_array as $column_name => $value) {
            $_SESSION[$this->form_name][$column_name] = $value;
        }
    }

    public function getPageTitle() {
        return $this->page_title;
    }

    public function getFormName() {
        return $this->form_name;
    }
}
