<?php

namespace Nodes\Api\Exceptions;

use Nodes\Exceptions\Exception as NodesException;

/**
 * Class InvalidUserAgent.
 */
class InvalidUserAgent extends NodesException
{
    /**
     * InvalidUserAgent constructor.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string  $message   Error message
     * @param  int $code      Error code
     * @param  array   $headers   List of headers
     * @param  bool $report    Wether or not exception should be reported
     * @param  string  $severity  Options: "fatal", "error", "warning", "info"
     */
    public function __construct($message, $code, $headers = [], $report = true, $severity = 'error')
    {
        parent::__construct($message, $code, $headers, $report, $severity);
    }
}
