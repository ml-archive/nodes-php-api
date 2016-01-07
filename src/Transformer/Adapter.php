<?php
namespace Nodes\Api\Transformer;

use Dingo\Api\Transformer\Adapter\Fractal as DingoAdapterFractal;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use Illuminate\Support\Collection as IlluminateCollection;
use Illuminate\Contracts\Pagination\Paginator as IlluminatePaginator;

/**
 * Class Fractal
 *
 * @package Nodes\Api\Transformer
 */
class Adapter extends DingoAdapterFractal
{
    /**
     * Create a Fractal resource instance
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param  mixed                                      $response
     * @param  \Nodes\Api\Transformer\TransformerAbstract $transformer
     * @param  array                                      $parameters
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\Collection
     */
    protected function createResource($response, $transformer, array $parameters)
    {
        // By default, all transformed data will be nested within a root key.
        // If you at some point, need to remove this root key, you'll need to
        // override the root key with the value "null".
        if (array_key_exists('key', $parameters) && is_null($parameters['key'])) {
            $key = null;
        } else {
            $key = !empty($parameters['key']) ? $parameters['key'] : config('nodes.api.transformer.fractal.serializer.rootKey', 'data');
        }

        if ($response instanceof IlluminatePaginator || $response instanceof IlluminateCollection) {
            return new FractalCollection($response, $transformer, $key);
        }

        return new FractalItem($response, $transformer, $key);
    }
}