<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title or 'No title' }}</title>
    <link rel="stylesheet" screen="media" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" screen="media" href="{{ asset('vendor/nodes/api/css/base.css') }}">
</head>

<body>
    @yield('content')
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="{{ asset('vendor/nodes/api/js/base.js') }}"></script>
</body>
</html>