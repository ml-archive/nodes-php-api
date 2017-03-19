<?php

namespace Nodes\Api\Http\Response;

use Closure;
use ErrorException;
use Dingo\Api\Http\Response\Factory as DingoHttpResponseFactory;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nodes\Api\Http\Response;
use Nodes\Exceptions\Exception;

/**
 * Class Factory.
 */
class Factory extends DingoHttpResponseFactory
{
    /**
     * Respond with a created response and associate a location if provided.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @param  string|null $location
     * @param null         $content
     * @return \Nodes\Api\Http\Response
     */
    public function created($location = null, $content = null)
    {
        $response = new Response($content);
        $response->setStatusCode(201);

        if (! is_null($location)) {
            $response->header('Location', $location);
        }

        return $response;
    }

    /**
     * Respond with an accepted response and associate a location and/or content if provided.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string|null $location
     * @param  mixed       $content
     * @return \Nodes\Api\Http\Response
     */
    public function accepted($location = null, $content = null)
    {
        $response = new Response($content);
        $response->setStatusCode(202);

        if (! is_null($location)) {
            $response->header('Location', $location);
        }

        return $response;
    }

    /**
     * Respond with a no content response.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return \Nodes\Api\Http\Response
     */
    public function noContent()
    {
        $response = new Response(null);

        return $response->setStatusCode(204);
    }

    /**
     * Bind a collection to a transformer and start building a response.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  \Illuminate\Support\Collection $collection
     * @param  object                         $transformer
     * @param  array                          $parameters
     * @param  \Closure                       $after
     * @return \Nodes\Api\Http\Response
     */
    public function collection(Collection $collection, $transformer, $parameters = [], Closure $after = null)
    {
        if ($collection->isEmpty()) {
            $class = get_class($collection);
        } else {
            $class = get_class($collection->first());
        }

        $binding = $this->transformer->register($class, $transformer, $parameters, $after);

        return new Response($collection, 200, [], $binding);
    }

    /**
     * Bind an item to a transformer and start building a response.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  object   $item
     * @param  object   $transformer
     * @param  array    $parameters
     * @param  \Closure $after
     * @return \Nodes\Api\Http\Response
     */
    public function item($item, $transformer, $parameters = [], Closure $after = null)
    {
        $class = get_class($item);

        $binding = $this->transformer->register($class, $transformer, $parameters, $after);

        return new Response($item, 200, [], $binding);
    }

    /**
     * Bind a paginator to a transformer and start building a response.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  \Illuminate\Contracts\Pagination\Paginator $paginator
     * @param  object                                     $transformer
     * @param  array                                      $parameters
     * @param  \Closure                                   $after
     * @return \Nodes\Api\Http\Response
     */
    public function paginator(Paginator $paginator, $transformer, array $parameters = [], Closure $after = null)
    {
        if ($paginator->isEmpty()) {
            $class = get_class($paginator);
        } else {
            $class = get_class($paginator->first());
        }

        $binding = $this->transformer->register($class, $transformer, $parameters, $after);

        return new Response($paginator, 200, [], $binding);
    }

    /**
     * Returns a response with an array of data.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  $data
     * @return \Nodes\Api\Http\Response
     */
    public function content($data)
    {
        return $this->array(['data' => $data]);
    }

    /**
     * Return an error response.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @param  string  $message
     * @param  int $statusCode
     * @param  string  $statusMessage
     * @throws \Nodes\Exceptions\Exception
     */
    public function error($message, $statusCode, $statusMessage = null)
    {
        throw new Exception($message, $statusCode, $statusMessage);
    }

    /**
     * Call magic methods beginning with "with".
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     * @throws \ErrorException
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'with')) {
            return call_user_func_array([$this, Str::camel(substr($method, 4))], $parameters);

        // Because PHP won't let us name the method "array" we'll simply watch for it
        // in here and return the new binding. Gross.
        } elseif ($method == 'array') {
            return new Response($parameters[0]);
        }

        throw new ErrorException('Undefined method '.get_class($this).'::'.$method);
    }
}
