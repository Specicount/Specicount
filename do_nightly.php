<?php
use phpformbuilder\database\Mysql;
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 6/10/2018
 * Time: 5:17 PM
 *
 * This is to be run nightly by cron
 * It backs up the database and removes all the password reset codes from the users table
 */

require_once '/var/www/html/phpformbuilder/database/Mysql.php';
require_once '/var/www/html/phpformbuilder/database/db-connect.php';

$db = new Mysql();
$db->updateRows("users", array("password_reset_code" => "NULL"));

// TODO do the backups (also how to backup pictures)

mkdir("/home/biodata/backups");

// sudo crontab -u biodata -e
// 30 23 * * * php /var/www/html/do_nightly.php

shell_exec("mysqldump -u root -pmymape123 --all-databases | gzip > /home/biodata/backups/biobase_`date '+%Y-%m-%d'`.sql.gz");