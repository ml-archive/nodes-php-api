<?php

namespace Nodes\Api\Tests;

use App;
use Nodes\Api\Auth\Auth;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Api controllers tests helper.
 *
 * Use within tests of api controllers to call api routes as authenticated user, or as a guest.
 *
 * Here is an inline example:
 * <code>
 * <?php
 * $this->being($user)->callApi('GET', '/api/users')->seeJson((new UserTransformer)->transform($user));
 * ?>
 * </code>
 */
trait TestHelperTrait
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Auth
     */
    protected $authMock;

    /**
     * Act as provided user.
     *
     * This simulates user being authenticated with token.
     *
     * User does not need to be saved to database.
     *
     * @param $user
     * @return $this
     */
    public function being($user)
    {
        $this->authMock = $this->getMockBuilder(Auth::class)->disableOriginalConstructor()->getMock();
        $this->authMock->method('user')->willReturn($user);
        App::instance('api.auth', $this->authMock);

        return $this;
    }

    /**
     * Visit the given URI with a JSON request.
     *
     * Api call will contain 'Accept' header required by nodes api package.
     *
     * To call api as authenticated user chain being() method first.
     *
     * @param  string $method
     * @param  string $uri
     * @param  array $data
     * @param  array $headers
     * @return $this
     */
    public function callApi($method, $uri, array $data = [], array $headers = [])
    {
        return $this->json($method, $uri, $data,
            array_merge(['Accept' => 'application/vnd.nodes.v'.$this->getApiVersion().'+json'], $headers));
    }

    /**
     * @author Robert Trzebinski <rotr@nodesagency.com>
     * Content of 'data' property of the JSON returned by API call
     * @return mixed
     */
    protected function responseData()
    {
        return json_decode($this->response->getContent())->data;
    }

    /**
     * @author Robert Trzebinski <rotr@nodesagency.com>
     * Content of 'meta' property of the JSON returned by API call
     * @return mixed
     */
    protected function responseMeta()
    {
        return json_decode($this->response->getContent())->meta;
    }

    /**
     * Get api version that is tested.
     *
     * @return mixed
     */
    abstract protected function getApiVersion();
}
