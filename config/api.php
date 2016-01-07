<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Standards Tree
    |--------------------------------------------------------------------------
    |
    | Versioning an API with Dingo revolves around content negotiation and
    | custom MIME types. A custom type will belong to one of three
    | standards trees, the Vendor tree (vnd), the Personal tree
    | (prs), and the Unregistered tree (x).
    |
    | By default the Unregistered tree (x) is used, however, should you wish
    | to you can register your type with the IANA. For more details:
    | https://tools.ietf.org/html/rfc6838
    |
    */

    'standardsTree' => env('API_STANDARDS_TREE', 'vnd'),

    /*
    |--------------------------------------------------------------------------
    | API Subtype
    |--------------------------------------------------------------------------
    |
    | Your subtype will follow the standards tree you use when used in the
    | "Accept" header to negotiate the content type and version.
    |
    | For example: Accept: application/x.SUBTYPE.v1+json
    |
    */

    'subtype' => env('API_SUBTYPE', 'nodes'),

    /*
    |--------------------------------------------------------------------------
    | Default API Version
    |--------------------------------------------------------------------------
    |
    | This is the default version when strict mode is disabled and your API
    | is accessed via a web browser. It's also used as the default version
    | when generating your APIs documentation.
    |
    */

    'version' => env('API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Default API Prefix
    |--------------------------------------------------------------------------
    |
    | A default prefix to use for your API routes so you don't have to
    | specify it for each group.
    |
    */

    'prefix' => env('API_PREFIX', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Default API Domain
    |--------------------------------------------------------------------------
    |
    | A default domain to use for your API routes so you don't have to
    | specify it for each group.
    |
    */

    'domain' => env('API_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Name
    |--------------------------------------------------------------------------
    |
    | When documenting your API using the API Blueprint syntax you can
    | configure a default name to avoid having to manually specify
    | one when using the command.
    |
    */

    'name' => env('API_NAME', null),

    /*
    |--------------------------------------------------------------------------
    | Conditional Requests
    |--------------------------------------------------------------------------
    |
    | Globally enable conditional requests so that an ETag header is added to
    | any successful response. Subsequent requests will perform a check and
    | will return a 304 Not Modified. This can also be enabled or disabled
    | on certain groups or routes.
    |
    */

    'conditionalRequest' => env('API_CONDITIONAL_REQUEST', true),

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | Enabling strict mode will require clients to send a valid Accept header
    | with every request. This also voids the default API version, meaning
    | your API will not be browsable via a web browser.
    |
    */

    'strict' => env('API_STRICT', true),

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
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Providers
    |--------------------------------------------------------------------------
    |
    | The authentication providers that should be used when attempting to
    | authenticate an incoming API request.
    |
    */

    'auth' => [
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
    ],

    /*
    |--------------------------------------------------------------------------
    | Throttling / Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Consumers of your API can be limited to the amount of requests they can
    | make. You can create your own throttles or simply change the default
    | throttles.
    |
    */

    'throttling' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Response Transformer
    |--------------------------------------------------------------------------
    |
    | Responses can be transformed so that they are easier to format. By
    | default a Fractal transformer will be used to transform any
    | responses prior to formatting. You can easily replace
    | this with your own transformer.
    |
    */

    'transformer' => [

        /*
        |--------------------------------------------------------------------------
        | Adapter
        |--------------------------------------------------------------------------
        |
        | Should Return an instance of a Transformer adapter.| Could either be
        | a Closure or the namespace of the adapter.
        |
        */
        'adapter' => function() {
            // Instantiate Fractal Manager
            $manager = new League\Fractal\Manager;

            // Set serializer
            $serializer = prepare_config_instance(config('nodes.api.transformer.fractal.serializer.class'));
            $manager->setSerializer($serializer);

            // Retrieve adapter namespace
            $adapter = env('API_TRANSFORMER', Nodes\Api\Transformer\Adapter::class);

            // Instantiate transformer
            return new $adapter(
                $manager,
                config('nodes.api.transformer.includeKey', 'include'),
                config('nodes.api.transformer.incldueSeparator', ','),
                config('nodes.api.transformer.eagerLoad', true)
            );
        },

        /*
        |--------------------------------------------------------------------------
        | Fractal
        |--------------------------------------------------------------------------
        |
        | These settings a specific to the Fractal adapter.
        | If you're not sure what these settings does, don't change them.
        |
        */
        'fractal' => [
            'serializer' => [
                'class' => env('API_TRANSFORMER_SERIALIZER', Nodes\Api\Transformer\Serializer::class),
                'rootKey' => 'data'
            ],
            'includeKey' => 'include',
            'includeSeparator' => ',',
            'eagerLoad' => true
        ]
    ],

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

    'defaultFormat' => env('API_DEFAULT_FORMAT', 'json'),

    'formats' => [

        'json' => Dingo\Api\Http\Response\Format\Json::class,

    ],
];