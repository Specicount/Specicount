<?php
/**
 * Created by IntelliJ IDEA.
 * User: Elliott
 * Date: 9/09/2018
 * Time: 12:41 AM
 */
use phpformbuilder\database\Mysql;
use phpformbuilder\Form;
use const phpformbuilder\database\DBNAME;

/**
 * @return mixed|string the system OS
 */
function getOS() {

    global $user_agent;

    $os_platform  = "Unknown OS Platform";

    $os_array     = array(
        '/windows nt 10/i'      =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows xp/i'         =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/windows me/i'         =>  'Windows ME',
        '/win98/i'              =>  'Windows 98',
        '/win95/i'              =>  'Windows 95',
        '/win16/i'              =>  'Windows 3.11',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
    );

    foreach ($os_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $os_platform = $value;

    return $os_platform;
}

/**
 * @return mixed|string the browser used to navigate to the page
 */
function getBrowser() {

    global $user_agent;

    $browser        = "Unknown Browser";

    $browser_array = array(
        '/msie/i'      => 'Internet Explorer',
        '/firefox/i'   => 'Firefox',
        '/safari/i'    => 'Safari',
        '/chrome/i'    => 'Chrome',
        '/edge/i'      => 'Edge',
        '/opera/i'     => 'Opera',
        '/netscape/i'  => 'Netscape',
        '/maxthon/i'   => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i'    => 'Handheld Browser'
    );

    foreach ($browser_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $browser = $value;

    return $browser;
}

/**
 * Returns the primary keys of the given table as an array of strings
 * @param $table_name string the table to query on
 * @return array the primary keys of the table
 */
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

/**
 * Returns the column column names of the given table as an array of strings
 * @param $table_name string the table to query on
 * @return array column names
 */
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
// Not currently used
/*function getVariablesToString($array) {
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
}*/

// Not currently used
/*function arrayToString($arr) {
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
}*/

/**
 * Get parent most script (used to test if login script)
 * E.g. if you navigated to server/index.php it would return /var/www/html/index.php
 * @return string
 */
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

/**
 * Store an error message to print out
 * @param $error_msg string the error message
 */
function storeErrorMsg($error_msg) {
    global $messages;
    $messages["error"][] = '<p class="alert alert-danger">'.$error_msg .'</p>';
}

/**
 * Store a success message to print out
 * @param $success_msg string the success message
 */
function storeSuccessMsg($success_msg) {
    global $messages;
    $messages["success"][] = '<p class="alert alert-success">'.$success_msg .'</p>';
}

/**
 * Gets the access level of the user ($email) in a project ($project_id)
 * If no arguments given, will return the access level of the logged in user for the project page they have navigated to
 * @param null|string $email the users email address
 * @param null|string $project_id the current project ID
 * @return bool|mixed
 */
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

/**
 * Get the login modal
 * This modal is used on each page when not logged in
 */
function getLoginModal () {
    // Login form
    $login_form = new Form("login", 'horizontal', 'novalidate', 'bs4');
    $login_form->startFieldset("Login");
    $login_form->setCols(0, 12);
    $login_form->addInput("hidden", "do-login", 1);
    $login_form->addHelper("Email", "email");
    $login_form->addInput('email', 'email', '', '', 'required');
    $login_form->addHelper("Password", "password");
    $login_form->addInput('password', 'password', '', '', 'required');
    $login_form->addHtml('<div style="margin-bottom: 10px"><a href=\'#\' data-remodal-target="modal-forgot-password-target"><i class="fa fa-key"></i> Forgot Password</a></div>');
    $login_form->addBtn('submit', 'submit-btn', "login", '<i class="fa fa-sign-in-alt"></i> Log In', 'class=btn btn-success ladda-button, data-style=zoom-in', 'lor-group');
    $login_form->addBtn('button', 'register-btn', "register", '<i class="fa fa-user-plus"></i> Register', 'class=btn btn-primary, data-style=zoom-in, onclick=navigate(\'register.php\')', 'lor-group');
    $login_form->printBtnGroup('lor-group');
    $login_form->modal('#modal-login-target');
    $login_form->addPlugin('formvalidation', '#login', 'bs4');
    $login_form->endFieldset();
    return $login_form;
}

/**
 * Get the login modal
 * This modal is used on each page when not logged in
 */
function getForgotPasswordModal () {
    // Login form
    $forgot_form = new Form("forgot_password", 'horizontal', 'novalidate', 'bs4');
    $forgot_form->startFieldset("Forgot Password");
    $forgot_form->setCols(0, 12);
    $forgot_form->addInput("hidden", "do-forgot-password", 1);
    $forgot_form->addHtml("<p style='font-style: italic'>Please enter your email to get a reset password link...</p>");
    $forgot_form->addHelper("Email", "email");
    $forgot_form->addInput('email', 'email', '', '', 'required');
    $forgot_form->addBtn('submit', 'send-btn', "send", '<i class="fa fa-envelope"></i> Send', 'class=btn btn-success ladda-button, data-style=zoom-in', 'lor-group');
    $forgot_form->printBtnGroup('lor-group');
    $forgot_form->modal('#modal-forgot-password-target');
    $forgot_form->addPlugin('formvalidation', '#login', 'bs4');
    $forgot_form->endFieldset();
    return $forgot_form;
}

/**
 * Send the forgotten password email to the a registered user
 * @param $actual_link string the link of the website
 */
function sendForgotPasswordEmail ($actual_link) {

    // Mail server to send email from
    $smtp_settings = array(
        'host' => 'smtp.gmail.com',
        'smtp_auth' => true,    // Enable SMTP authentication
        'username' => 'anu.biodata@gmail.com',     // SMTP username
        'password' => 'Bio123Data',     // SMTP password
        'smtp_secure' => 'tls',  // Enable TLS encryption, `ssl` also accepted
        'port' => 587
    );

    // Get the forgot password email
    $db = new Mysql();
    $db->querySingleRowArray("SELECT * FROM users WHERE email = ". Mysql::SQLValue($_POST["email"]));
    $user_data = $db->recordsArray()[0];
    if ($user_data && !$db->error()) {
        // Generate a random code to be used on the email
        // Does not matter how it's generated just that it's
        // random since it is reset once the user has reset their password
        $pass = substr(md5(uniqid(mt_rand(), true)) , 0, 8);

        // Add the password in the database for that user
        $db->updateRows("users", array("password_reset_code" => Mysql::SQLValue($pass)), array("email" => Mysql::SQLValue($user_data["email"])));
        if (!$db->error()) {
            // If the code is and added to the database, send the user an email of the password reset link
            // Replacements made in email template
            $replacements = array(
                "name" => $user_data["first_name"],
                "action_url" => $actual_link . "/password_reset.php?email=" . rawurlencode($user_data["email"]) . "&reset_code=" . rawurlencode($pass), // the code
                "operating_system" => getOS(),
                "browser_name" => getBrowser(),
                "support_url" => "mailto:&#097;&#110;&#117;&#046;&#098;&#105;&#111;&#100;&#097;&#116;&#097;&#064;&#103;&#109;&#097;&#105;&#108;&#046;&#099;&#111;&#109;?subject=Password Reset"
            );
            // The email data
            $email_config = array(
                'sender_email' => 'anu.biodata@gmail.com',
                'sender_name' => 'Specicount',
                'recipient_email' => addslashes($user_data["email"]),
                'subject' => 'Specicount Password Reset',
                'template' => 'password_reset.html',
                'custom_replacements' => $replacements,
                'debug' => true
            );
            $sent_message = "";

            // Send the email
            try {
                $sent_message = Form::sendMail($email_config, $smtp_settings);
            } catch (Exception $e) {
                storeErrorMsg("Could not send email!");
            }
            if (stripos($sent_message, "error")) {
                storeErrorMsg($sent_message);
            } else {
                storeSuccessMsg("Email sent successfully!");
            }
        } else {
            storeErrorMsg("Could not add code to table");
        }
    } else {
        storeErrorMsg("Email address not found!");
    }
}