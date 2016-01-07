<?php
namespace Nodes\Api\Auth\Exceptions;

use Nodes\Exceptions\Exception as NodesException;

/**
 * Class UnauthorizedException
 *
 * @package Nodes\Api\Auth\Exceptions
 */
class UnauthorizedException extends NodesException
{
    /**
     * TokenExpiredException constructor
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  string   $message
     * @param  integer  $code
     * @param  array    $headers
     * @param  boolean  $report
     * @param  string   $severity
     */
    public function __construct($message, $code = 401, array $headers = [], $report = false, $severity = 'error')
    {
        parent::__construct($message, $code, $headers, $report, $severity);

        // Set status code
        $this->setStatusCode(401);
    }
}
