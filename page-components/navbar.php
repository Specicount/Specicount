<style type="text/css">
    fieldset {
        margin-bottom: 80px;
    }

    * {
        box-sizing: border-box;
    }

    .btn-group a{
        margin:0px;
        width: 100%;
        border: 0px;
        padding: 10px 0px;
        font-size: 14px;
        cursor: pointer;
        text-align: center;
        display: inline-block;
    }

    .ribbon-button {
        background-color: #718a6b;
        color: white;
        text-decoration: none;
    }

    .ribbon-button:hover{
        color: white;
    }

    /* Clear floats (clearfix hack) */
    .btn-group:after {
        content: "";
        clear: both;
        display: table;
    }

    /* Add a background color on hover */
    .ribbon-button:hover, .dropdown:hover .ribbon-button {
        background-color: #5e7359;
        text-decoration: none;
    }

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
    }
    .dropdown-content a{
        color: black;
        background-color: #f9f9f9;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        width: 100%;
    }
    .dropdown-content a:hover {
        background-color: #f1f1f1;
        text-decoration: none;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }
</style>
<nav>
    <div class="btn-group" style="width: 100%; font-size: 0; min-width: 510px;">
        <a class="ribbon-button" href="index.php" style="width:20%">Home</a>
        <div class="dropdown" style="width:20%">
            <a href="projects.php"  class="ribbon-button" style="width:100%">Projects</a>
            <div class="dropdown-content" style="width:100%">
                <a href="projects.php">My Projects</a>
                <a href="#Recent_Projects">Recent Projects</a>
                <a href="add_new_project.php">New Project</a>
                <a href="#Share_Project">Share Project</a>
            </div>
        </div>
        <a class="ribbon-button" href="search_specimen.php" style="width:20%">Search</a>
        <div class="dropdown" style="width:20%">
        <a class="ribbon-button" href="tools.php">Tools</a>
            <div class="dropdown-content">
                <a href="#tool1">Tool 1</a>
                <a href="#tool2">Tool 2</a>
                <a href="#tool3">Tool 3</a>
            </div>
        </div>
        <div class="dropdown" style="width:20%">
            <a href="help.php"  class="ribbon-button">Help</a>
            <div class="dropdown-content">
                <a href="#About">About</a>
                <a href="#Usage">How To Use</a>
                <a href="#FAQ">Frequently Asked Questions</a>
                <a href="logout.php">[Temporary] Logout</a>
            </div>
        </div>
    </div>
</nav>

<!--<nav class="navbar navbar-expand">
    <a class="sidebar-toggle text-light mr-3"><i class="fa fa-bars"></i></a>
    <text class="text-center"><?= $navbar_text?></text>
</nav>-->
