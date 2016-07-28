<?php

namespace Nodes\Api\Auth\Exceptions;

use Nodes\Exceptions\Exception as NodesException;

/**
 * Class MissingUserModelException.
 */
class MissingUserModelException extends NodesException
{
    /**
     * MissingUserModelException constructor.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string   $message
     * @param  int  $code
     * @param  array    $headers
     * @param  bool  $report
     * @param  string   $severity
     */
    public function __construct($message = 'Missing user model for authentication', $code = 500, array $headers = [], $report = false, $severity = 'error')
    {
        parent::__construct($message, $code, $headers, $report, $severity);

        // Set status code and status message
        $this->setStatusCode(500, 'Missing user model for authentication');
    }
}
