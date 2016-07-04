<?php
/*
|--------------------------------------------------------------------------
| Authentication Providers
|--------------------------------------------------------------------------
|
| The authentication providers that should be used when attempting to
| authenticate an incoming API request.
|
*/
return [

    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | Reference the namespace of the user model, which we should use to
    | perform our authentication sequence on.
    |
    */
    'model' => null,

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Array of available auth providers. All providers will get a run-through
    | unless an authentication has been successful in one of them.
    |
    */
    'providers' => [
        'userToken' => Nodes\Api\Auth\Providers\UserToken::class,
        'masterToken' => Nodes\Api\Auth\Providers\MasterToken::class
    ],

    /*
    |--------------------------------------------------------------------------
    | User token settings
    |--------------------------------------------------------------------------
    |
    | Settings for the "userToken" authentication provider.
    | If you don't know what these settings does, then don't change them.
    |
    */
    'userToken' => [

        /*
        |--------------------------------------------------------------------------
        | Database table
        |--------------------------------------------------------------------------
        |
        | Name of database where user tokens are located.
        |
        */
        'table' => 'user_tokens',

        /*
        |--------------------------------------------------------------------------
        | Columns
        |--------------------------------------------------------------------------
        |
        | Mapping of columns. These are needed to reference owner of token,
        | the unique token it self and the expire time of token.
        |
        */
        'columns' => [
            'user_id' => 'user_id',
            'token' => 'token',
            'expire' => 'expire'
        ],

        /*
        |--------------------------------------------------------------------------
        | Lifetime
        |--------------------------------------------------------------------------
        |
        | Set the lifetime of a token. This used by used as a "literal" time.
        | I.e. "+1 week" or "+1 month".
        |
        | @see http://php.net/manual/en/datetime.formats.relative.php
        |
        */
        'lifetime' => null
    ],

    /*
    |--------------------------------------------------------------------------
    | Master token settings
    |--------------------------------------------------------------------------
    |
    | Settings for the "masterToken" authentication provider.
    | If you don't know what these settings does, then don't change them.
    |
    */
    'masterToken' => [

        /*
        |--------------------------------------------------------------------------
        | Enable / Disable
        |--------------------------------------------------------------------------
        |
        | By default the master token is disabled for security reasons.
        |
        */
        'enabled' => false,

        /*
        |--------------------------------------------------------------------------
        | Salt
        |--------------------------------------------------------------------------
        |
        | The unique identifier which is used to generate the master token.
        |
        */
        'salt' => 'nodes+' . env('APP_KEY', 'nodes'),

        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        |
        | Settings used to retrieve an user associated with master token.
        |
        | 'column' is the field that we should look for in the 'users' table.
        | 'operator' is the operator in your where condition. Usually '=' is fine.
        | 'value' is the value we expect before we retrieve the user.
        |
        */
        'user' => [
            'column' => 'master',
            'operator' => '=',
            'value' => 1
        ]
    ]

];