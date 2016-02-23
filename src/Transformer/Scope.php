<?php
namespace Nodes\Api\Transformer;

use InvalidArgumentException;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\NullResource as FractalNullResource;
use League\Fractal\Scope as FractalScope;
use League\Fractal\Serializer\SerializerAbstract as FractalSerializerAbstract;
use League\Fractal\TransformerAbstract as FractalTransformerAbstract;
use Nodes\Api\Transformer\Resources\Content as NodesResourceContent;
use Nodes\Api\Transformer\TransformerAbstract as NodesTransformerAbstract;

/**
 * Class Scope
 *
 * @package Nodes\Api\Transformers
 */
class Scope extends FractalScope
{
    /**
     * Determine if a transformer has any available includes
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param  \Nodes\Api\Transformer\TransformerAbstract|\League\Fractal\TransformerAbstract|callable $transformer
     * @return boolean
     */
    protected function transformerHasIncludes($transformer)
    {
        if (!$transformer instanceof NodesTransformerAbstract &&
            !$transformer instanceof FractalTransformerAbstract) {
            return false;
        }

        $defaultIncludes = $transformer->getDefaultIncludes();
        $availableIncludes = $transformer->getAvailableIncludes();

        return !empty($defaultIncludes) || !empty($availableIncludes);
    }

    /**
     * Serialize a resource
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  \League\Fractal\Serializer\SerializerAbstract $serializer
     * @param  mixed                                         $data
     * @return array
     */
    protected function serializeResource(FractalSerializerAbstract $serializer, $data)
    {
        $resourceKey = $this->resource->getResourceKey();

        if ($this->resource instanceof FractalCollection) {
            return $serializer->collection($resourceKey, $data);
        } elseif ($this->resource instanceof FractalItem) {
            return $serializer->item($resourceKey, $data);
        } elseif ($this->resource instanceof NodesResourceContent) {
            return (!empty($resourceKey)) ? [$resourceKey => $data] : $data;
        }

        return $serializer->null();
    }

    /**
     * Execute the resources transformer and return the data and included data
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function executeResourceTransformers()
    {
        $transformer = $this->resource->getTransformer();
        $data = $this->resource->getData();

        $transformedData = $includedData = [];

        if ($this->resource instanceof FractalItem) {
            list($transformedData, $includedData[]) = $this->fireTransformer($transformer, $data);
        } elseif ($this->resource instanceof FractalCollection) {
            foreach ($data as $value) {
                list($transformedData[], $includedData[]) = $this->fireTransformer($transformer, $value);
            }
        } elseif ($this->resource instanceof FractalNullResource) {
            $transformedData = null;
            $includedData = [];
        } elseif ($this->resource instanceof NodesResourceContent) {
            $transformedData = (array) $data;
            $includedData = [];
        } else {
            throw new InvalidArgumentException(
                'Argument $resource should be an instance of League\Fractal\Resource\Item'
                .' or League\Fractal\Resource\Collection'
            );
        }

        return [$transformedData, $includedData];
    }
}