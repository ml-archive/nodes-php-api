<?php

namespace Nodes\Api\Auth\Exceptions;

use Nodes\Exceptions\Exception;

/**
 * Class ResetPasswordNoUserException.
 */
class ResetPasswordNoUserException extends Exception
{
    /**
     * ResetPasswordNoUserException constructor.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string   $message
     * @param  int  $code
     * @param  array    $headers
     * @param  bool  $report
     * @param  string   $severity
     */
    public function __construct($message = 'No user found', $code = 445, array $headers = [], $report = false, $severity = 'error')
    {
        parent::__construct($message, $code, $headers, $report, $severity);

        // Set status code and status message
        $this->setStatusCode(445);
    }
}
