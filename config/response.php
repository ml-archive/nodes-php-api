<?php
/*
|--------------------------------------------------------------------------
| Response Formats
|--------------------------------------------------------------------------
|
| Responses can be returned in multiple formats by registering different
| response formatters. You can also customize an existing response
| formatter.
|
*/
return [

    'defaultFormat' => env('API_DEFAULT_FORMAT', 'json'),

    'formats' => [

        'json' => Dingo\Api\Http\Response\Format\Json::class,

    ],

];
