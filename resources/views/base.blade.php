<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title or 'No title' }}</title>
    <link rel="stylesheet" screen="media" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <style type="text/css">
        body { background: #eaeaea; }
        #nBox { width: 80vw; max-width: 80vh; max-width: 450px; display: none; }
    </style>
</head>

<body>
@yield('content')
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<script>
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
</script>
</body>
</html>