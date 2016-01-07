<?php
namespace Nodes\Api\Transformer;

use League\Fractal\Serializer\DataArraySerializer as FractalSerializerDataArray;

/**
 * Class Fractal
 *
 * @package Nodes\Api\Transformer
 */
class Serializer extends FractalSerializerDataArray
{
    /**
     * Serialize a collection
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  string $resourceKey
     * @param  array  $data
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return $resourceKey ? [$resourceKey => $data] : $data;
    }

    /**
     * Serialize an item
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  string $resourceKey
     * @param  array  $data
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        return $resourceKey ? [$resourceKey => $data] : $data;
    }
}