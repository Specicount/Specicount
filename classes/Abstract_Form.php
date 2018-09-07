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

abstract class Abstract_Form {

    private $form_type;  // E.g. project, core, sample etc
    private $form_name;  // A string that uniquely identifies the form for phpformbuilder
    private $page_title; // What gets displayed on the browser tab and on the navbar
    private $table_name;

    public abstract function getFormType(); //Should return a string, e.g. 'project', 'core', 'sample', etc
    public abstract function getTableName(); //Should return a string, e.g. 'projects', 'cores', 'samples', etc
    public abstract function submit();
    public abstract function delete();
    public abstract function deleteMsgSuccess();
    public abstract function deleteMsgFail();
    public abstract function initializeVariables();

    public function __construct() {
        $this->form_type = strtolower($this->getFormType()); //strtolower just in case of bad data
        $this->table_name = $this->getTableName();
        if ($_GET["edit"]) {
            $this->form_name = 'add-new-'.$this->form_type.'-edit';
            $this->page_title = "Edit ".ucwords($this->form_type).' '.$_GET["edit"];
        } else {
            $this->form_name = 'add-new-'.$this->form_type;
            $this->page_title = 'Add New '.ucwords($this->form_type);
        }
    }

    public function getFormName() {
        return $this->form_name;
    }

    public function execute() {
        //Make some variables global so that other class functions can access them
        global $db, $msg;

        $db = new Mysql();



        // If the form has been posted (saved, deleted, etc) back to the server
        if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($this->form_name) === true) {
            // If the delete button was pressed
            if ($_POST["submit-btn"] == "delete") {
                // Call the form-specific delete function
                $this->delete();
                if ($db->error()) {
                    $msg = '<p class="alert alert-danger">'.$this->deleteMsgFail().'</p>' . '\n';
                } else {
                    $msg = '<p class="alert alert-success">'.ucwords($this->form_type).' deleted successfully!</p>' . ' \n';
                    header("Location: index.php");
                }
            //Else update or insert new rows into db
            } else {
                $validator = Form::validate($this->form_name);

                if ($validator->hasErrors()) {
                    $_SESSION['errors'][$this->form_name] = $validator->getAllErrors();
                } else {

                    // Get all the column names of the given table
                    $sql =  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS".
                            "WHERE TABLE_SCHEMA = '".cconstant(DBNAME)."'".  // Constant defined in db-connect.php
                            "AND TABLE_NAME = '".$this->table_name."'";
                    $db->query($sql);

                    // Create an array which stores the new values to update for each column
                    foreach ($db->recordsArray() as $row) {
                        $column_name = $row['COLUMN_NAME'];
                        $update[$column_name] = Mysql::SQLValue($_POST[$column_name]);
                    }

                    //Get the primary keys of the given table
                    $sql =  "SELECT k.column_name".
                            "FROM information_schema.table_constraints t".
                            "JOIN information_schema.key_column_usage k".
                            "USING(constraint_name,table_schema,table_name)".
                            "WHERE t.constraint_type='PRIMARY KEY'".
                                "AND t.table_schema= '".constant(DBNAME)."'".
                                "AND t.table_name='".$this->table_name."'";
                    $db->query($sql);

                    // Create an array which stores the submitted values of the primary keys to identify which row to update
                    foreach ($db->recordsArray() as $row) {
                        $primary_key = $row['column_name'];
                        $filter[$primary_key] = $update[$primary_key];
                    }

                    if ($_GET["edit"]) {
                        $db->updateRows($this->table_name, $update, $filter);
                    } else {
                        $db->insertRow($this->table_name, $update);
                    }

                    if (!empty($db->error())) {
                        $msg = '<p class="alert alert-danger">' . $db->error() . '<br>' . $db->getLastSql() . '</p>' . "\n";
                    } else {
                        $msg = '<p class="alert alert-success">Database updated successfully !</p>' . " \n";
                    }
                }
            }
        }

        // If editing, then fill in the fields with the current database values
        if ($_GET["edit"]) {
            //$primary_keys = array_values($db->recordsArray()[0]['column_name']);
            unset($_SESSION[$this->form_name]);
            $core_id = trim($_GET["edit"]);
            $db->selectRows($this->table_name, array("core_id" => Mysql::SQLValue($core_id), 'project_name' => Mysql::SQLValue($project)));
            $core = $db->recordsArray()[0];
            $_SESSION[$this->form_name]["core_id"] = $core["core_id"];
            $_SESSION[$this->form_name]["core_description"] = $core["description"];
        }

    }
}
