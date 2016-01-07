<?php
namespace Nodes\Api\Events;

use Nodes\Api\Http\Response;

/**
 * Class ResponseIsMorphing
 *
 * @package Nodes\Api\Event
 */
class ResponseIsMorphing
{
    /**
     * Response instance
     *
     * @var \Nodes\Api\Http\Response
     */
    public $response;

    /**
     * Response content
     *
     * @var string
     */
    public $content;

    /**
     * Create a new response is morphing event. Content is passed by reference
     * so that multiple listeners can modify content
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  \Nodes\Api\Http\Response $response
     * @param  string                   $content
     */
    public function __construct(Response $response, &$content)
    {
        $this->response = $response;
        $this->content = &$content;
    }
}
