<?php
/*
|--------------------------------------------------------------------------
| API Middleware
|--------------------------------------------------------------------------
|
| Middleware that will be applied globally to all API requests.
| The will be added AFTER the ones setup in app/Http/Kernal.php
|
*/
return [
    \Nodes\Api\Http\Middleware\SSL::class,
    \Nodes\Api\Http\Middleware\Meta::class,
];
