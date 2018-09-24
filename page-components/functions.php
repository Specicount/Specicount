<?php
/**
 * Created by IntelliJ IDEA.
 * User: Elliott
 * Date: 9/09/2018
 * Time: 12:41 AM
 */
namespace functions;
use phpformbuilder\database\Mysql;
use const phpformbuilder\database\DBNAME;

// Returns the primary keys of the given table as an array of strings
function getPrimaryKeys($table_name) {
    $db = new Mysql();
    $sql =  "SELECT k.column_name ".
            "FROM information_schema.table_constraints t ".
            "JOIN information_schema.key_column_usage k ".
            "USING(constraint_name,table_schema,table_name) ".
            "WHERE t.constraint_type='PRIMARY KEY' ".
                "AND t.table_schema='".constant('DBNAME')."' ".
                "AND t.table_name='".$table_name."'";
    $db->query($sql);
    // Create an array which stores the posted values of the primary keys to identify which row to update
    foreach ($db->recordsArray() as $row) {
        $primary_keys[] = $row['column_name'];
    }
    return $primary_keys;
}
// Returns the column column names of the given table as an array of strings
function getColumnNames($table_name) {
    $db = new Mysql();
    $sql =  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS ".
            "WHERE TABLE_SCHEMA = '".constant('DBNAME')."' ".  // Constant defined in db-connect.php
            "AND TABLE_NAME = '".$table_name."'";
    $db->query($sql);

    // Create an array which stores the new values to update for each column
    foreach ($db->recordsArray() as $row) {
        $column_names[] = $row['COLUMN_NAME'];
    }
    return $column_names;
}

// Takes an associative array of GET variables and their values and concatenates them for easy placement in URLs
function getVariablesToString($array) {
    $get_variables = "";
    foreach ($array as $key => $value) {
        $get_variables .= $key."=".$value;
        end($array); // Move internal array pointer to last element
        // If current key is the last key, then don't output & since there may be no more GET variables to append
        if ($key !== key($array)) {
            $get_variables .= "&";
        }
    }
    return $get_variables;
}

function arrayToString($arr) {
    $str = '(';
    $last_key = end(array_keys($arr));
    foreach($arr as $key => $value) {
        $value = str_replace("'", "", $value);
        $str .= $key.'=>'.$value;
        if ($key != $last_key) {
            $str .= ', ';
        }
    }
    $str .= ')';
    return $str;
}


// Get parent most script (used to test if login script)
// E.g. if you navigated to server/index.php it would return /var/www/html/index.php
function getTopMostScript() {
    $backtrace = debug_backtrace(
        defined("DEBUG_BACKTRACE_IGNORE_ARGS")
            ? DEBUG_BACKTRACE_IGNORE_ARGS
            : FALSE);
    $top_frame = array_pop($backtrace);
    return basename($top_frame['file']);
}


/**
 * Stores a success or fail message to the user based on the results of a database query
 * @param Mysql $db
 * @param null $error_msg setting an error message will override the default database error message
 * @param null $success_msg set a success message or none will get printed
 * @return bool whether the database operation succeeded or not
 */
function storeDbMsg($db, $success_msg = null, $error_msg = null) {
    // If the database has thrown any errors
    if ($db->error()) {
        // If a fail message hasn't been set
        if ($error_msg == null) {
            // Set the fail message to the given database error
            $error_msg = $db->error() . '<br>' . $db->getLastSql();
        }
        storeErrorMsg($error_msg);
        return false;
    } else {
        if (isset($success_msg)) {
            storeSuccessMsg($success_msg);
        }
        return true;
    }
}

function storeErrorMsg($error_msg) {
    global $messages;
    $error_msg_html = '<p class="alert alert-danger">'.$error_msg .'</p>';
    $messages["error"][] = $error_msg_html;
}

function storeSuccessMsg($success_msg) {
    global $messages;
    $success_msg_html = '<p class="alert alert-success">'.$success_msg .'</p>';
    $messages["success"][] = $success_msg_html;
}

// Gets the access level of the user ($email) in a project ($project_id)
// If no arguments given, will return the access level of the logged in user for the project page they have navigated to
function getAccessLevel($email = null, $project_id = null) {
    if (isset($_SESSION["email"])) {

        if (!isset($project_id)) {
            if (isset($_GET["project_id"])) {
                $project_id = $_GET["project_id"];
            } else {
                return false;
            }
        }

        if (!$email) {
            if (isset($_SESSION["email"])) {
                $email = $_SESSION["email"];
            } else {
                return false;
            }
        }


        $db = new Mysql();
        $filter["project_id"] = Mysql::sqlValue($project_id);
        $filter["email"] = Mysql::sqlValue($email);
        $sql = "SELECT access_level FROM user_project_access ".Mysql::buildSQLWhereClause($filter);
        $my_access_level = $db->querySingleValue($sql);
        return $my_access_level;
    } else {
        return false;
    }

}