<?php
/**
 * Created by IntelliJ IDEA.
 * User: Matthew
 * Date: 15/03/2018
 * Time: 3:53 PM
 */

if (!isset($form)) {
    echo "<p style='color: red'>FATAL ERROR: form not set</p>";
    exit;
}

if (empty($title)) {
    $title = "UNTITLED";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $title ?></title>
    <?php
    require_once "header.php";
    if (is_array($form)) {
        foreach ($form as $f) {
            $f->printIncludes('css');
        }
    } else {
        $form->printIncludes('css');
    }
    ?>
</head>
<?php
$navbar_text = $title;
require_once "navbar.php"; // Add Side Nav Bar
?>
<div class="d-flex">
    <?php
    require_once "sidebar.php"; // Add Side Nav Bar
    ?>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div style="padding-top: 30px" class="col-lg-11 col-md-11 col-sm-11 col-xs-11">
                <?php
                if (isset($sent_message)) {
                    echo $sent_message;
                } elseif (isset($msg)) {
                    echo $msg;
                }
                if (is_array($form)) {
                    foreach ($form as $f) {
                        $f->render();
                    }
                } else {
                    $form->render();
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php
require_once "scripts.php"; // Get scripts
?>
<?php
if (is_array($form)) {
    foreach ($form as $f) {
        $f->printIncludes('js');
        $f->printJsCode();
    }
} else {
    $form->printIncludes('js');
    $form->printJsCode();
}
?>
</html>
