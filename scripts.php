<script src="js/jquery-3.3.1.js"></script>
<script src="js/jquery.hoverIntent.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/popper.js"></script>
<script src="js/bsadmin.js"></script>
<script src="js/fontawesome-all.js"></script>
<script>
$(document).ready(function() {
    // This uses the hoverIntent jquery plugin to avoid excessive queuing of animations
    // If mouse intends to hover over specimen
    $(".specimen-container").hoverIntent(
        function() {
            var spec_id = $(this).attr('id').split("_")[0];
            fadeInOverlay(spec_id);
        },
        function() {
            var spec_id = $(this).attr('id').split("_")[0];
            fadeOutOverlay(spec_id);
        });

    //If close button on overlay clicked
    $(".overlay .close-btn").click(function() {
        var spec_id = $(this).attr('id').split("_")[0];
        fadeOutOverlay(spec_id);
    });

    function fadeInOverlay(spec_id) {
        $("#"+spec_id+"_overlay").fadeIn(200);
        $("#"+spec_id+"_counter").fadeOut(200);
    }

    function fadeOutOverlay(spec_id) {
        $("#"+spec_id+"_overlay").fadeOut(200);
        $("#"+spec_id+"_counter").fadeIn(200);
    }

    $("p.alert").click(function() {
        $(this).fadeOut(200);
    });
});
</script>