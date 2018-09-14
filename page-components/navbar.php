<nav class="navbar navbar-expand">
    <div class="navbar-nav mr-auto">
        <?php
        if ($render_side) {
            echo "<a class=\"sidebar-toggle text-light mr-3\"><i class=\"fa fa-bars\"></i></a>";
        }
        ?>
        <text class="text-center"><?= $navbar_text?></text>
    </div>
    <?php
        // If a user is logged in
        if (isset($_SESSION["username"])) {
            echo '<a style="color:white; margin-right: 20px;text-decoration: none;" href="logout.php"><i class="fa fa-sign-out-alt"></i> Log Out</a>';
        } else {
            echo "<a style=\"color:white; margin-right: 20px;text-decoration: none;\" href='#' data-remodal-target=\"modal-login-target\"><i class=\"fa fa-sign-in-alt\"></i> Log In</a>";
        }
    ?>
</nav>