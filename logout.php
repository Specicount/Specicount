<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 26/08/2018
 * Time: 2:49 PM
 */

session_start();
if (isset($_SESSION['email'])) {
    unset($_SESSION['email']);
}
header("Location: index.php");