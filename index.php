<?php
require_once 'phpformbuilder/database/db-connect.php';
require_once 'phpformbuilder/database/Mysql.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home</title>
    <?php
    require_once "header.php"; // Get stylesheets, metadata, etc.
    ?>
</head>
<?php
$navbar_text = "Project Home";
require_once "navbar.php"; // Add top nav bar
?>
<div class="d-flex">
    <?php
    require_once "sidebar.php"; // Add side nav bar
    ?>
    <div class="container">
        <div class="row justify-content-center">
            <div style="padding-top: 30px" class="col-md-11 col-lg-10">
                <p style="font-style: italic">Create a project to get started</p>
            </div>
        </div>
    </div>
</div>
<?php
require_once "scripts.php"; // Get scripts
?>
</html>