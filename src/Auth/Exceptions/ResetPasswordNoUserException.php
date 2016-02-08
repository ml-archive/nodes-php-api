<?php
namespace Nodes\Api\Auth\Exceptions;

use Nodes\Exceptions\Exception;

/**
 * Class ResetPasswordNoUserException
 *
 * @package Nodes\Auth\Exception
 */
class ResetPasswordNoUserException extends Exception
{
    /**
     * ResetPasswordNoUserException constructor
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
    public function __construct($message = 'No user found', $code = 445, array $headers = [], $report = false, $severity = 'error')
    {
        parent::__construct($message, $code, $headers, $report, $severity);

        // Set status code and status message
        $this->setStatusCode(445);
    }
}