<?php
return [

    /*
   |--------------------------------------------------------------------------
   | Debug Mode
   |--------------------------------------------------------------------------
   |
   | Enabling debug mode will result in error responses caused by thrown
   | exceptions to have a "debug" key that will be populated with
   | more detailed information on the exception.
   |
   */
    'debug' => env('API_DEBUG', env('APP_DEBUG')),

    /*
    |--------------------------------------------------------------------------
    | Generic Error Format
    |--------------------------------------------------------------------------
    |
    | When some HTTP exceptions are not caught and dealt with the API will
    | generate a generic error response in the format provided. Any
    | keys that aren't replaced with corresponding values will be
    | removed from the final response.
    |
    */
    'errorFormat' => [
        'message' => ':message',
        'code' => ':code',
        'errors' => ':errors',
        'debug' => ':debug',
    ]

];