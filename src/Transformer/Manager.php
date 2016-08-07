<?php

namespace Nodes\Api\Transformer;

use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope as FractalScope;
use League\Fractal\Manager as FractalManager;

/**
 * Class Manager.
 */
class Manager extends FractalManager
{
    /**
     * Main method to kick this all off.
     * Make a resource then pass it over, and use toArray().
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  \League\Fractal\Resource\ResourceInterface $resource
     * @param  string                                     $scopeIdentifier
     * @param  \League\Fractal\Scope                      $parentScopeInstance
     * @return \Nodes\Api\Transformer\Scope
     */
    public function createData(ResourceInterface $resource, $scopeIdentifier = null, FractalScope $parentScopeInstance = null)
    {
        $scopeInstance = new Scope($this, $resource, $scopeIdentifier);

        // Update scope history
        if ($parentScopeInstance !== null) {
            // This will be the new children list of parents (parents parents, plus the parent)
            $scopeArray = $parentScopeInstance->getParentScopes();
            $scopeArray[] = $parentScopeInstance->getScopeIdentifier();

            $scopeInstance->setParentScopes($scopeArray);
        }

        return $scopeInstance;
    }
}
