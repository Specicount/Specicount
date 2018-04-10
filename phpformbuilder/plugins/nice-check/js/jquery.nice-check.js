/*=========================================================
            nice checkboxes and radio buttons for jQuery
            Author : Gilles Migliori
            Version : 1.0
=========================================================*/

/*

$('#form').niceCheck();

*/

(function($)
{
    $.fn.niceCheck = function(options){
        var settings = $.extend({
            // defaults settings.
            checkClassName: 'check'
        }, options );
        var $checkElement = $('<span>').addClass(settings.checkClassName);
        var $inputTargets = $(this).find('input[type="checkbox"], input[type="radio"]');
        $inputTargets.each(function() {
            $checkElement = $('<span>').addClass(settings.checkClassName);
            $('label[for="' + $(this).attr('id') + '"]').append($checkElement);
        });
    };
})(jQuery);