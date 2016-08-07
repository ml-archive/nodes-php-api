<?php

namespace Nodes\Api\Auth;

use Dingo\Api\Auth\Auth as DingoAuth;
use Nodes\Api\Auth\Exceptions\UnauthorizedException;
use Nodes\Exceptions\Exception as NodesException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class Auth.
 */
class Auth extends DingoAuth
{
    /**
     * Authenticate request.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  array $providers
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Nodes\Exceptions\Exception
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function authenticate(array $providers = [])
    {
        // Caught exceptions
        $exceptionStack = [];

        // Spin through each of the registered authentication providers and attempt to
        // authenticate through one of them. This allows a developer to implement
        // and allow a number of different authentication mechanisms.
        foreach ($this->filterProviders($providers) as $provider) {
            try {
                // Authenticate user with current provider
                $user = $provider->authenticate($this->router->getCurrentRequest(), $this->router->getCurrentRoute());

                // Set successful provider
                $this->providerUsed = $provider;

                return $this->user = $user;
            } catch (NodesException $exception) {
                $exceptionStack[] = $exception;
            } catch (UnauthorizedHttpException $exception) {
                $exceptionStack[] = $exception;
            } catch (BadRequestHttpException $exception) {
                // We won't add this exception to the stack as it's thrown when the provider
                // is unable to authenticate due to the correct authorization header not
                // being set. We will throw an exception for this below.
            }
        }

        $this->throwUnauthorizedException($exceptionStack);
    }

    /**
     * Throw the first exception from the exception stack.
     *
     * @author Morten Rugaard <moru@nods.dk>
     *
     * @param  array $exceptionStack
     * @return void
     * @throws \Nodes\Api\Auth\Exceptions\UnauthorizedException
     */
    protected function throwUnauthorizedException(array $exceptionStack)
    {
        $exception = array_shift($exceptionStack);

        if ($exception === null) {
            $exception = new UnauthorizedException('Failed to authenticate because of bad credentials or an invalid authorization header');
        }

        throw $exception;
    }

    /**
     * Extend the authentication layer with a custom provider.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string          $key
     * @param  object|callable $provider
     * @return \Nodes\Api\Auth
     */
    public function extend($key, $provider)
    {
        parent::extend($key, $provider);

        return $this;
    }
}
