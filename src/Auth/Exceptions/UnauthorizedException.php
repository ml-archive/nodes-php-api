<?php

namespace Nodes\Api\Auth\Exceptions;

use Nodes\Exceptions\Exception as NodesException;

/**
 * Class UnauthorizedException.
 */
class UnauthorizedException extends NodesException
{
    /**
     * TokenExpiredException constructor.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string   $message
     * @param  int  $code
     * @param  array    $headers
     * @param  bool  $report
     * @param  string   $severity
     */
    public function __construct($message, $code = 401, array $headers = [], $report = false, $severity = 'error')
    {
        parent::__construct($message, $code, $headers, $report, $severity);

        // Set status code
        $this->setStatusCode(401);
    }
}
