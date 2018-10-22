<?php

/* database connection */

/*if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') {
    define('DBUSER', 'root');
    define('DBPASS', 'mymape123');
    define('DBHOST', 'localhost');
    define('DBNAME', 'BioBase');
} else {
    define('DBUSER', 'db-user');
    define('DBPASS', 'db-pass');
    define('DBHOST', 'your-db-host.com');
    define('DBNAME', 'db-name');
}*/

define('DBUSER', 'root');
define('DBPASS', 'mymape123');
define('DBHOST', 'localhost');
define('DBNAME', 'BioBase');

define('DB', 'mysql:host=' . DBHOST . ';dbname=' . DBNAME);