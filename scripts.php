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
(function($) {
    $.fn.nodoubletapzoom = function() {
        $(this).bind('touchstart', function preventZoom(e) {
            var t2 = e.timeStamp
                , t1 = $(this).data('lastTouch') || t2
                , dt = t2 - t1
                , fingers = e.originalEvent.touches.length;
            $(this).data('lastTouch', t2);
            if (!dt || dt > 500 || fingers > 1) return; // not double-tap

            e.preventDefault(); // double tap - prevent the zoom
            // also synthesize click events we just swallowed up
            $(this).trigger('click').trigger('click');
        });
    };
})(jQuery);
</script>