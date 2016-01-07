<?php
namespace Nodes\Api\Routing;

use Dingo\Api\Http\Request as DingoRequest;
use Dingo\Api\Routing\Router as DingoRouter;
use Illuminate\Http\Response as IlluminateResponse;
use Nodes\Api\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

/**
 * Class Router
 *
 * @package Nodes\Api\Routing
 */
class Router extends DingoRouter
{
    /**
     * Register a resource controller
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  string $name
     * @param  string $controller
     * @param  array  $options
     * @return void
     */
    public function resource($name, $controller, array $options = [])
    {
        if ($this->container->bound('Nodes\Api\Routing\ResourceRegistrar')) {
            $registrar = $this->container->make('Nodes\Api\Routing\ResourceRegistrar');
        } else {
            $registrar = new ResourceRegistrar($this);
        }

        $registrar->register($name, $controller, $options);
    }

    /**
     * Prepare a response by transforming and formatting it correctly
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  mixed                   $response
     * @param  \Dingo\Api\Http\Request $request
     * @param  string                  $format
     * @return \Nodes\Api\Http\Response
     */
    protected function prepareResponse($response, DingoRequest $request, $format)
    {
        if ($response instanceof IlluminateResponse) {
            $response = Response::makeFromExisting($response);
        }

        if ($response instanceof Response) {
            // If we try and get a formatter that does not exist we'll let the exception
            // handler deal with it. At worst we'll get a generic JSON response that
            // a consumer can hopefully deal with. Ideally they won't be using
            // an unsupported format.
            try {
                $response->getFormatter($format)->setResponse($response)->setRequest($request);
            } catch (NotAcceptableHttpException $exception) {
                return $this->exception->handle($exception);
            }

            $response = $response->morph($format);
        }

        if ($response->isSuccessful() && $this->requestIsConditional()) {
            if (! $response->headers->has('ETag')) {
                $response->setEtag(md5($response->getContent()));
            }

            $response->isNotModified($request);
        }

        return $response;
    }
}