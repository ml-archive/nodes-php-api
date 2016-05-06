<?php
namespace Nodes\Api\Exceptions;

use App;
use Exception;
use Nodes\Api\Http\Response;
use Illuminate\Support\Facades\Log;
use Dingo\Api\Contract\Debug\MessageBagErrors;
use Nodes\Exceptions\Exception as NodesException;
use Dingo\Api\Exception\Handler as DingoExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class Handler
 *
 * @see Dingo\Api\Exception\Handler
 * @package Nodes\Api\Exception
 */
class Handler extends DingoExceptionHandler
{

    /**
     * Array of exceptions not to report
     *
     * @var array
     */
    protected $dontReport = [
        'Nodes\Api\Auth\Exception\InvalidTokenException',
        'Nodes\Api\Auth\Exception\MissingTokenException',
        'Nodes\Api\Auth\Exception\TokenExpiredException'
    ];

    /**
     * Report and log an exception
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  Exception $e
     * @throws Exception
     */
    public function report(Exception $e)
    {
        try {
            if ($e instanceof NodesException && $e->getReport()) {
                app('nodes.bugsnag')->notifyException($e, $e->getMeta(), $e->getSeverity());
            } elseif (! $e instanceof NodesException) {
                app('nodes.bugsnag')->notifyException($e, null, 'error');
            }
        } catch (Exception $e) {
            // Do nothing
        }

        Log::error($e);
    }

    /**
     * Handle an exception if it has an existing handler
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @access public
     * @param \Exception $exception
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function handle(Exception $exception)
    {
        // If we are in configured environments, just throw the exception. Useful for unit testing
        if (in_array(app()->environment(), config('nodes.api.errors.throwOnEnvironment', []))) {
            throw $exception;
        }

        $this->report($exception);

        foreach ($this->handlers as $hint => $handler) {
            if (! $exception instanceof $hint) {
                continue;
            }

            if ($response = $handler($exception)) {
                if (! $response instanceof Response) {
                    $response = new Response($response, $this->getExceptionStatusCode($exception));
                }

                return $response;
            }
        }

        return $this->genericResponse($exception);
    }

    /**
     * Handle a generic error response if there is no handler available
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param \Exception $exception
     * @throws \Exception
     * @return \Illuminate\Http\Response
     */
    protected function genericResponse(Exception $exception)
    {
        $replacements = $this->prepareReplacements($exception);

        $response = $this->newResponseArray();

        array_walk_recursive($response, function (&$value, $key) use ($exception, $replacements) {
            if (starts_with($value, ':') && isset($replacements[$value])) {
                $value = $replacements[$value];
            }
        });

        $response = $this->recursivelyRemoveEmptyReplacements($response);

        return new Response($response, $this->getExceptionStatusCode($exception), $this->getHeaders($exception));
    }

    /**
     * Prepare the replacements array by gathering the keys and values.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param \Exception $exception
     * @return array
     */
    protected function prepareReplacements(Exception $exception)
    {
        // Retrieve status code of exception
        $statusCode = $this->getStatusCode($exception);

        // Retrieve message of exception
        //
        // If the exception does not have a message,
        // we'll fallback to a message of the status code and status message.
        if (! $message = $exception->getMessage()) {
            $message = sprintf('%d: %s', $statusCode, Response::$statusTexts[$statusCode]);
        }

        // Base replacements
        $replacements = [
            ':message' => $message,
            ':code'    => $statusCode,
        ];

        // If exception contains a message bag of errors
        // we'll add them to the replacements array
        if (($exception instanceof NodesException || $exception instanceof MessageBagErrors) && $exception->hasErrors()) {
            $replacements[':errors'] = $exception->getErrors();
        }

        // If we're running in debug mode,
        // we'll add much more detailed information
        // extracted from the exception.
        if ($this->runningInDebugMode()) {
            $replacements[':debug'] = [
                'class' => get_class($exception),
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        return array_merge($replacements, $this->replacements);
    }

    /**
     * Get the exception status code
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  \Exception $exception
     * @param  integer    $defaultStatusCode
     * @return integer
     */
    protected function getExceptionStatusCode(Exception $exception, $defaultStatusCode = 500)
    {
        if ($exception instanceof NodesException) {
            return [
                $exception->getStatusCode(),
                $exception->getStatusMessage()
            ];
        } elseif ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        } else {
            return $defaultStatusCode;
        }
    }
}
