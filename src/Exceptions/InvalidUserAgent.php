<?php
namespace Nodes\Api\Exceptions;

use Nodes\Exceptions\Exception as NodesException;

/**
 * Class InvalidUserAgent
 *
 * @package Nodes\Api\Exceptions
 */
class InvalidUserAgent extends NodesException
{
    /**
     * InvalidUserAgent constructor
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  string  $message   Error message
     * @param  integer $code      Error code
     * @param  array   $headers   List of headers
     * @param  boolean $report    Wether or not exception should be reported
     * @param  string  $severity  Options: "fatal", "error", "warning", "info"
     */
    public function __construct($message, $code, $headers = [], $report = true, $severity = 'error')
    {
        parent::__construct($message, $code, $headers, $report, $severity);
    }
}