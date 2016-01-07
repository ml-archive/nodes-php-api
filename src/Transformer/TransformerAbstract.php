<?php
namespace Nodes\Api\Transformer;

use League\Fractal\Scope as FractalScope;
use League\Fractal\TransformerAbstract as FractalTransformerAbstract;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\NullResource as FractalNullResource;
use League\Fractal\Resource\Item as FractalItem;

/**
 * Class TransformerAbstract
 *
 * @package Nodes\Api\Transformer
 */
abstract class TransformerAbstract extends FractalTransformerAbstract
{
    /**
     * Figure out which includes we need
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access private
     * @param  \League\Fractal\Scope $scope
     * @return array
     */
    private function figureOutWhichIncludes(FractalScope $scope)
    {
        $includes = $this->getDefaultIncludes();
        foreach ($this->getAvailableIncludes() as $include) {
            if ($scope->isRequested($include)) {
                $includes[] = $include;
            }
        }

        return $includes;
    }

    /**
     * This method is fired to loop through available includes, see if any of
     * them are requested and permitted for this scope
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  \League\Fractal\Scope $scope
     * @param  mixed                 $data
     * @return array
     */
    public function processIncludedResources(FractalScope $scope, $data)
    {
        $includedData = [];

        $includes = $this->figureOutWhichIncludes($scope);

        foreach ($includes as $include) {
            $includedData = $this->includeResourceIfAvailable(
                $scope,
                $data,
                $includedData,
                $include
            );
        }

        return $includedData === [] ? false : $includedData;
    }

    /**
     * Include a resource only if it is available on the method
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access private
     * @param  \League\Fractal\Scope $scope
     * @param  mixed  $data
     * @param  array  $includedData
     * @param  string $include
     * @return array
     */
    private function includeResourceIfAvailable(
        FractalScope $scope,
        $data,
        $includedData,
        $include
    ) {
        if ($resource = $this->callIncludeMethod($scope, $include, $data)) {
            $childScope = $scope->embedChildScope($include, $resource);

            $includedData[$include] = (!$childScope->getResource() instanceof FractalNullResource) ? $childScope->toArray() : null;
        }

        return $includedData;
    }

    /**
     * Create a new item resource object
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param  mixed                                              $data
     * @param  \Nodes\Api\Transformer\TransformerAbsract|callable $transformer
     * @param  string                                             $resourceKey
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    protected function item($data, $transformer, $resourceKey = null)
    {
        if (empty($data)) {
            return new FractalNullResource;
        }

        return new FractalItem($data, $transformer, $resourceKey);
    }

    /**
     * Create a new collection resource object
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param  mixed                                              $data
     * @param  \Nodes\Api\Transformer\TransformerAbsract|callable $transformer
     * @param  string                                             $resourceKey
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    protected function collection($data, $transformer, $resourceKey = null)
    {
        if (empty($data)) {
            return new FractalNullResource;
        }

        return new FractalCollection($data, $transformer, $resourceKey);
    }
}