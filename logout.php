<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 26/08/2018
 * Time: 2:49 PM
 */
session_start();
if (isset($_SESSION['auth_user']))
{
    unset($_SESSION['auth_user']);
}

header("Location: login.php");