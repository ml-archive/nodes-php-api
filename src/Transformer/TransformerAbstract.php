<?php
namespace Nodes\Api\Transformer;

use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\NullResource as FractalNullResource;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\ResourceAbstract as FractalResourceAbstract;
use Nodes\Api\Transformer\Resources\Content as NodesResourceContent;

/**
 * Class TransformerAbstract
 *
 * @package Nodes\Api\Transformer
 */
abstract class TransformerAbstract
{
    /**
     * Resources that can be included if requested
     *
     * @var array
     */
    protected $availableIncludes = [];

    /**
     * Include resources without needing it to be requested
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * The transformer should know about the current scope,
     * so we can fetch relevant params
     *
     * @var \Nodes\Api\Transformer\Scope
     */
    protected $currentScope;

    /**
     * Getter for availableIncludes
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return array
     */
    public function getAvailableIncludes()
    {
        return $this->availableIncludes;
    }

    /**
     * Getter for defaultIncludes
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return array
     */
    public function getDefaultIncludes()
    {
        return $this->defaultIncludes;
    }

    /**
     * Getter for currentScope
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return \Nodes\Api\Transformer\Scope
     */
    public function getCurrentScope()
    {
        return $this->currentScope;
    }

    /**
     * Figure out which includes we need
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access private
     * @param  \Nodes\Api\Transformer\Scope $scope
     * @return array
     */
    private function figureOutWhichIncludes(Scope $scope)
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
     * @param  \Nodes\Api\Transformer\Scope $scope
     * @param  mixed                        $data
     * @return array
     */
    public function processIncludedResources(Scope $scope, $data)
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
     * @param  \Nodes\Api\Transformer\Scope $scope
     * @param  mixed                        $data
     * @param  array                        $includedData
     * @param  string                       $include
     * @return array
     */
    private function includeResourceIfAvailable(Scope $scope, $data, $includedData, $include) {
        if ($resource = $this->callIncludeMethod($scope, $include, $data)) {
            $childScope = $scope->embedChildScope($include, $resource);
            $includedData[$include] = (!$childScope->getResource() instanceof FractalNullResource) ? $childScope->toArray() : null;
        }

        return $includedData;
    }

    /**
     * Call Include Method
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param  Scope  $scope
     * @param  string $includeName
     * @param  mixed  $data
     * @return \League\Fractal\Resource\ResourceInterface
     * @throws \Exception
     */
    protected function callIncludeMethod(Scope $scope, $includeName, $data)
    {
        $scopeIdentifier = $scope->getIdentifier($includeName);
        $params = $scope->getManager()->getIncludeParams($scopeIdentifier);

        // Check if the method name actually exists
        $methodName = 'include'.str_replace(' ', '', ucwords(str_replace('_', ' ', str_replace('-', ' ', $includeName))));

        $resource = call_user_func([$this, $methodName], $data, $params);

        if ($resource === null) {
            return false;
        }

        if (!$resource instanceof FractalResourceAbstract) {
            throw new \Exception(sprintf(
                'Invalid return value from %s::%s(). Expected %s, received %s.',
                __CLASS__,
                $methodName,
                'League\Fractal\Resource\ResourceAbstract',
                gettype($resource)
            ));
        }

        return $resource;
    }

    /**
     * Setter for availableIncludes
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  array $availableIncludes
     * @return $this
     */
    public function setAvailableIncludes($availableIncludes)
    {
        $this->availableIncludes = $availableIncludes;
        return $this;
    }

    /**
     * Setter for defaultIncludes
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  array $defaultIncludes
     * @return $this
     */
    public function setDefaultIncludes($defaultIncludes)
    {
        $this->defaultIncludes = $defaultIncludes;
        return $this;
    }

    /**
     * Setter for currentScope
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  \Nodes\Api\Transformer\Scope $currentScope
     * @return $this
     */
    public function setCurrentScope($currentScope)
    {
        $this->currentScope = $currentScope;
        return $this;
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

    /**
     * Create a new content (array) resource object
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  array $data
     * @param  string $resourceKey
     * @return \Nodes\Api\Transformer\Resources\Content|\League\Fractal\Resource\NullResource
     */
    protected function content(array $data, $resourceKey = null)
    {
        if (empty($data)) {
            return new FractalNullResource;
        }

        return new NodesResourceContent($data, null, $resourceKey);
    }
}