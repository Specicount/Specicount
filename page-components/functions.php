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