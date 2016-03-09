<?php
namespace Nodes\Api\Http;

use Dingo\Api\Transformer\Binding;
use InvalidArgumentException;
use Illuminate\Http\Response as IlluminateResponse;
use Dingo\Api\Http\Response as DingoResponse;
use Nodes\Api\Events\ResponseIsMorphing;
use Nodes\Api\Events\ResponseWasMorphed;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class Response
 *
 * @package Nodes\Api\Http
 */
class Response extends DingoResponse
{
    /**
     * Create a new response instance
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  mixed                          $content
     * @param  integer|array                  $status
     * @param  array                          $headers
     * @param  \Dingo\Api\Transformer\Binding $binding
     */
    public function __construct($content, $status = 200, $headers = [], Binding $binding = null)
    {
        // Set headers
        $this->headers = new ResponseHeaderBag($headers);

        // Set content
        $this->setContent($content);

        // Set status code and text
        if (is_array($status)) {
            $this->setStatusCode($status[0], !empty($status[1]) ? $status[1] : null);
        } else {
            $this->setStatusCode($status);
        }

        // Set protocol version
        $this->setProtocolVersion('1.0');

        // If "Date" header is missing, we'll set one.
        if (!$this->headers->has('Date')) {
            $this->setDate(\DateTime::createFromFormat('U', time(), new \DateTimeZone('UTC')));
        }

        // Transformer binding
        $this->binding = $binding;
    }

    /**
     * Make an API response from an existing response object
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  \Illuminate\Http\Response $old
     * @return \Nodes\Api\Http\Response
     */
    public static function makeFromExisting(IlluminateResponse $old)
    {
        // Support for custom status code and message
        $statusCode = ($old instanceof Response) ? $old->getStatusCodeAndMessage() : $old->getStatusCode();

        // Generate API response from response object
        $new = static::create($old->getOriginalContent(), $statusCode);
        $new->headers = $old->headers;
        return $new;
    }

    /**
     * Fire the morphed event
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function fireMorphedEvent()
    {
        if (!static::$events) {
            return;
        }
        static::$events->fire(new ResponseWasMorphed($this, $this->content));
    }

    /**
     * Fire the morphing event
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function fireMorphingEvent()
    {
        if (!static::$events) {
            return;
        }
        static::$events->fire(new ResponseIsMorphing($this, $this->content));
    }

    /**
     * Is status code invalid?
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return bool
     */
    public function isInvalid()
    {
        return $this->statusCode < 100 && $this->statusCode <= 997;
    }

    /**
     * Sets the response status code (and message if provided)
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  integer $statusCode HTTP status code
     * @param  string  $text       HTTP status text
     * @return \Nodes\Api\Http\Response
     * @throws \InvalidArgumentException
     */
    public function setStatusCode($statusCode, $text = null)
    {
        // Fallback status code message if missing
        if (!empty($text)) {
            $statusText = $text;
        } elseif (!empty(self::$statusTexts[$statusCode])) {
            $statusText = self::$statusTexts[$statusCode];
        } else {
            $statusText = 'Undefined code';
        }

        // Set status code and text
        $this->statusCode = $statusCode;
        $this->statusText = $statusText;

        // Validate status code
        if ($this->isInvalid()) {
            throw new InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $statusCode));
        }

        return $this;
    }

    /**
     * Get status code and message
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return array
     */
    public function getStatusCodeAndMessage()
    {
        return [
            $this->statusCode,
            $this->statusText
        ];
    }
}