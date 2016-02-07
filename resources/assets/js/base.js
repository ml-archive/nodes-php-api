$(document).ready(function() {
    // Position element center of viewport
    var positionViewportCenter = function(element) {
        // Viewport dimensions
        var viewportWidth = $(window).width();
        var viewportHeight = $(window).height();

        // Element dimensions
        var elementWidth = element.outerWidth();
        var elementHeight = element.outerHeight();

        // Set new position for element
        element.css({
            top: ((viewportHeight - elementHeight) / 2),
            left: ((viewportWidth - elementWidth) / 2),
            position: 'relative'
        }).show();
    };

    // Position in the center of viewport
    // and re-position to center on resize
    $(window).resize(function() {
        $('.nodes-center').each(function() {
            positionViewportCenter($(this));
        })
    }).resize();
});