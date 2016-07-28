<?php

namespace Nodes\Api\Auth\Exceptions;

use Nodes\Exceptions\Exception as NodesException;

/**
 * Class UnauthorizedException.
 */
class UnVerifiedException extends NodesException
{
    /**
     * UnVerifiedException constructor.
     */
    public function __construct()
    {
        parent::__construct('User is not verified', 446);

        // Set status code
        $this->setStatusCode(446, 'Unverified');
        $this->dontReport();
    }
}
