<?php
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
return [

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
        $manager = new Nodes\Api\Transformer\Manager;

        // Set serializer
        $serializer = prepare_config_instance(config('nodes.api.transformer.fractal.serializer.class'));
        $manager->setSerializer($serializer);

        // Retrieve adapter namespace
        $adapter = env('API_TRANSFORMER', Nodes\Api\Transformer\Adapter::class);

        // Instantiate transformer
        return new $adapter(
            $manager,
            config('nodes.api.transformer.fractal.includeKey', 'include'),
            config('nodes.api.transformer.fractal.includeSeparator', ','),
            config('nodes.api.transformer.fractal.eagerLoad', true)
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

];
