<?php
ini_set('display_errors', 1);

/*==========================================================
=            Protection against unauthorized access            =
==========================================================*/

/*  TO AUTHORIZE ACCESS:
                -   REPLACE false WITH true LINE 15
                -   OPEN (OR REFRESH) THIS FILE IN YOUR BROWSER

    IMPORTANT:  WHEN YOU HAVE FINISHED REPLACE true WITH false on LINE 15 TO LOCK THE ACCESS.
                THIS FILE MUST NOT STAY UNLOCKED ON YOUR PRODUCTION SERVER;
*/
define('AUTHORIZE', false);

/*=====  End of Protection against unauthorized access  ======*/

if(AUTHORIZE === true) {
    $form_class_path = dirname(__FILE__);
    $plugins_path = $form_class_path . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR;

    // reliable document_root (https://gist.github.com/jpsirois/424055)
    $root_path = str_replace($_SERVER['SCRIPT_NAME'],'',$_SERVER['SCRIPT_FILENAME']);

    // reliable document_root with symlinks resolved
    $info = new \SplFileInfo($root_path);

    // var_dump($info);
    $real_root_path = $info->getRealPath();

    // sanitize directory separator
    $form_class_path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $form_class_path);
    $real_root_path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $real_root_path);

    $plugins_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . ltrim(str_replace(array($real_root_path, DIRECTORY_SEPARATOR), array('', '/'), $plugins_path), '/');

    $current_dir = str_replace(basename(__FILE__), '', $_SERVER['SCRIPT_NAME']);
    $phpformbuilder_include_code = 'include_once rtrim($_SERVER[\'DOCUMENT_ROOT\'], DIRECTORY_SEPARATOR) . \'' . $current_dir . 'Form.php\';';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Php Form Builder - Help file for include and other paths</title>
    <meta name="description" content="Php Form Builder - Help file for include and other paths">

    <!-- Bootstrap CSS -->
        <link href="../documentation/assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Documentation CSS -->
    <link href="../documentation/assets/css/documentation.css" rel="stylesheet">
    <!-- code prettify -->
    <link rel="stylesheet" href="../documentation/assets/js/prism/prism.min.css" media="screen">
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <nav id="main-nav" class="navbar navbar-inverse">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#main-nav-content">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="../index.html">
            <img alt="Php Form Builder" title="Php Form Builder" src="../documentation/assets/images/phpformbuilder-logo-txt.png">
            </a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div id="main-nav-content" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="../index.html">Home</a></li>
                <li><a href="../documentation/beginners-guide.html"><span class="hidden-sm">PHP </span>Beginners Guide</a></li>
                <li><a href="../documentation/quick-start-guide.html">Quick Start Guide</a></li>
                <li><a href="../templates/index.php">Templates</a></li>
                <li><a href="../documentation/contact-form-in-2-minutes.php">Contact form in 2 minutes</a></li>
                <li><a href="../documentation/how-to.php">How To ... ?</a></li>
                <li><a href="../documentation/class-doc.html">Class Doc.</a></li>
                <li><a href="../documentation/functions-reference.html">Functions Reference</a></li>
                <li class="active"><a href="../documentation/help-center.html">Troubleshooting</a></li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </nav>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <?php if(AUTHORIZE === true) { ?>
                    <h1 class="text-center">Php Form Builder - Help file for include and other paths</h1>
                    <hr>
                    <section class="card">
                        <h2><label class="label label-success">Solve Error 500</label> The correct include statement to include <strong>Form.php</strong> is the following:</h2>
                        <code class="language-php"><?php echo $phpformbuilder_include_code; ?></code>
                        <?php if($phpformbuilder_include_code != 'include_once rtrim($_SERVER[\'DOCUMENT_ROOT\'], DIRECTORY_SEPARATOR) . \'/phpformbuilder/Form.php\';') { ?>
                        <hr>
                        <p><strong>You have to replace the following code:<br> <code class="language-php">include_once rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . '/phpformbuilder/Form.php';</code><br>in your forms with the correct include statement shown above.</strong></p>
                        <?php } else { ?>
                            <p><strong>This is the default path used in all templates. You've got nothing to change.</strong></p>
                        <?php } // end if ?>
                        <p class="alert alert-danger has-icon">Don't forget to revert <strong><em>true</em></strong> to <strong><em>false</em></strong> Line 15 to protect this file against unauthorized access</p>
                    </section>
                    <section class="card">
                        <h2>The variables below provide some useful debugging information about your server configuration</h2>
                        <dl class="dl-horizontal">
                            <dt>$plugins_path</dt>
                            <dd><code class="small"><?php echo $plugins_path; ?></code></dd>
                            <dd class="line-break"></dd>
                            <dt>$_SERVER['SCRIPT_NAME']</dt>
                            <dd><code class="small"><?php echo $_SERVER['SCRIPT_NAME']; ?></code></dd>
                            <dd class="line-break"></dd>
                            <dt>$_SERVER['SCRIPT_FILENAME']</dt>
                            <dd><code class="small"><?php echo $_SERVER['SCRIPT_FILENAME']; ?></code></dd>
                            <dd class="line-break"></dd>
                            <dt>$root_path</dt>
                            <dd><code class="small"><?php echo $root_path; ?></code></dd>
                            <dd class="line-break"></dd>
                            <dt>$real_root_path</dt>
                            <dd><code class="small"><?php echo $real_root_path; ?></code></dd>
                            <dd class="line-break"></dd>
                            <dt>$form_class_path</dt>
                            <dd><code class="small"><?php echo $form_class_path; ?></code></dd>
                            <dd class="line-break"></dd>
                            <dt>$real_root_path</dt>
                            <dd><code class="small"><?php echo $real_root_path; ?></code></dd>
                            <dd class="line-break"></dd>
                            <dt>$plugins_url</dt>
                            <dd><code class="small"><?php echo $plugins_url; ?></code></dd>
                            <dd class="line-break"></dd>
                        </dl>
                    </section>
                <?php } else { ?>
                    <p class="alert alert-warning has-icon">This file is protected against unauthorized access.</p>
                    <p><strong>To allow access and display information, open this file in your code editor and replace <span class="label label-default">false</span> with <span class="label label-default">true</span> Line 15.</p>
                <?php } // end if ?>
            </div>
        </div>
    </div>
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <!-- Bootstrap 4 JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
</body>
</html>
