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

    public $name;
    private $db, $db_columns;

    public abstract function setName();
    public abstract function submit();
    public abstract function delete();
    public abstract function initializeVariables();

    public function __construct($name) {
        $this->setName();

    }

    public function initialize() {

        session_start();
        include_once 'phpformbuilder/Form.php';
        require_once 'phpformbuilder/database/db-connect.php';
        require_once 'phpformbuilder/database/Mysql.php';

        $this->initializeVariables();

        $this->db = new Mysql();

        if ($_GET["edit"]) {
            $form_name = 'add-new-'.$this->name.'-edit';
            $page_name = "Edit ".ucwords($this->name).' '.$_GET["edit"];
        } else {
            $form_name = 'add-new-'.$this->name;
            $page_name = 'Add New '.ucwords($this->name);
        }


        if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken($form_name) === true) {
            if ($_POST["submit-btn"] == "delete") {
                $this->delete();
            } else {
                $validator = Form::validate($form_name);

                if ($validator->hasErrors()) {
                    $_SESSION['errors'][$form_name] = $validator->getAllErrors();
                } else {
                    $update["core_id"] = Mysql::SQLValue($_POST["core_id"]);
                    $update["project_name"] = Mysql::SQLValue($project);
                    $update["description"] = Mysql::SQLValue($_POST["core_description"]);

                    if ($_GET["edit"]) {
                        $this->db->updateRows('cores', $update, array("core_id" => $update["core_id"], 'project_name' => $update["project_name"]));
                    } else {
                        $this->db->insertRow('cores', $update);
                    }

                    if (!empty($db->error())) {
                        $msg = '<p class="alert alert-danger">' . $db->error() . '<br>' . $db->getLastSql() . '</p>' . "\n";
                    } else {
                        $msg = '<p class="alert alert-success">Database updated successfully !</p>' . " \n";
                    }
                }
            }
        }

        if ($_GET["edit"]) {
            unset($_SESSION['add-new-core-edit']);
            $core_id = trim($_GET["edit"]);
            $db->selectRows('cores', array("core_id" => Mysql::SQLValue($core_id), 'project_name' => Mysql::SQLValue($project)));
            $core = $db->recordsArray()[0];
            $_SESSION[$form_name]["core_id"] = $core["core_id"];
            $_SESSION[$form_name]["core_description"] = $core["description"];
        }

    }
}
