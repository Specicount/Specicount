<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 26/08/2018
 * Time: 2:49 PM
 */

session_start();
if (isset($_SESSION['username'])) {
    unset($_SESSION['username']);
    unset($_SESSION['username']);
}
header("Location: index.php");