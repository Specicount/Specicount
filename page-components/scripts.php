<!--This is the necessary scripts for each page-->
<script src="js/jquery-3.3.1.js"></script>
<script src="js/jquery.hoverIntent.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/popper.js"></script>
<script src="js/bsadmin.js"></script>
<script src="js/fontawesome-all.js"></script>
<!--The following script is for the overlays for each specimen on the counting page (fiddly and a bit ugly but somewhat works be wary if playing with)-->
<script>
$(document).ready(function() {
    // This uses the hoverIntent jquery plugin to avoid excessive queuing of animations
    // If mouse intends to hover over specimen
    $(".specimen-container").hoverIntent(
        function() {
            var specimen_id = $(this).attr('id').split("_")[0];
            fadeInOverlay(specimen_id);
        },
        function() {
            var specimen_id = $(this).attr('id').split("_")[0];
            fadeOutOverlay(specimen_id);
        });

    //If close button on overlay clicked
    $(".overlay .close-btn").click(function() {
        var specimen_id = $(this).attr('id').split("_")[0];
        fadeOutOverlay(specimen_id);
    });

    function fadeInOverlay(specimen_id) {
        $("#"+specimen_id+"_overlay").fadeIn(200);
        $("#"+specimen_id+"_counter").fadeOut(200);
    }

    function fadeOutOverlay(specimen_id) {
        $("#"+specimen_id+"_overlay").fadeOut(200);
        $("#"+specimen_id+"_counter").fadeIn(200);
    }

    $("p.alert").click(function() {
        $(this).fadeOut(200);
    });
});
</script>