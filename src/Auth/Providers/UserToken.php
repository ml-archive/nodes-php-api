<?php

namespace Nodes\Api\Auth\Providers;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Route;
use Dingo\Api\Contract\Auth\Provider as DingoAuthContract;
use Nodes\Api\Auth\Exceptions\InvalidTokenException;
use Nodes\Api\Auth\Exceptions\MissingUserModelException;
use Nodes\Api\Auth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class UserToken.
 */
class UserToken implements DingoAuthContract
{
    /**
     * User model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $userModel;

    /**
     * Token from "Authorization" header.
     *
     * @var string
     */
    protected $token;

    /**
     * Token table name.
     *
     * @var string
     */
    protected $tokenTable;

    /**
     * Token columns used in condition.
     *
     * @var array
     */
    protected $tokenColumns = [];

    /**
     * Token lifetime.
     *
     * @var int
     */
    protected $tokenLifetime = 0;

    /**
     * Auth constructor.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     */
    public function __construct()
    {
        // Set user model
        $this->userModel = prepare_config_instance(config('nodes.api.auth.model', null));

        // Set token table
        $this->setTokenSettings();
    }

    /**
     * Set token settings.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @return \Nodes\Api\Auth\Authorization
     */
    protected function setTokenSettings()
    {
        // Set token lifetime
        $this->tokenLifetime = config('nodes.api.auth.userToken.lifetime', null);

        // Set token table
        $this->tokenTable = $tokenTable = config('nodes.api.auth.userToken.table', 'user_tokens');

        // Set token columns used in condition
        $columns = config('nodes.api.auth.userToken.columns', [
            'user_id' => 'user_id',
            'token' => 'token',
            'expire' => 'expire',
        ]);

        // Prepend token table name to token columns
        foreach ($columns as $key => $field) {
            $this->tokenColumns[$key] = $tokenTable.'.'.$field;
        }

        return $this;
    }

    /**
     * Authenticate by token.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @param  \Illuminate\Http\Request $request
     * @param  \Dingo\Api\Routing\Route $route
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Nodes\Api\Auth\Exceptions\InvalidTokenException
     * @throws \Nodes\Api\Auth\Exceptions\TokenExpiredException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function authenticate(Request $request, Route $route)
    {
        // Validate "Authorization" header
        if (empty($request->header('authorization'))) {
            throw new BadRequestHttpException;
        }

        // Set token from "Authorization" header
        $this->token = (string) $request->header('authorization');

        // Authenticate user by token
        $user = $this->getUserByToken();
        if (empty($user)) {
            throw new InvalidTokenException('No user associated with provided token');
        }

        // If an expire time has been set in config
        // we need to validate and maybe update it as well
        if ($this->hasTokenLifetime()) {
            // Validate tokens expiry date
            if (strtotime($user->token->expire) < time()) {
                throw new TokenExpiredException('Token has expired');
            }

            // Update expire date
            $this->updateTokenExpiry();
        }

        return $user;
    }

    /**
     * Retrieve user by token.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return mixed|null
     */
    protected function getUserByToken()
    {
        // Look up in cache and return if found
        if ($user = cache_get('api.userToken', $this->getCacheParams())) {
            return $user;
        }

        // Look up in database
        $user = $this->generateQuery()->first();

        // Add to cache
       if ($user) {
           cache_put('api.userToken', $this->getCacheParams(), $user);
       }

        return $user;
    }

    /**
     * getCacheParams.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return array
     */
    private function getCacheParams()
    {
        return [
            'accessToken' => $this->getToken(),
        ];
    }

    /**
     * Update token's expire time.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @return bool
     */
    protected function updateTokenExpiry()
    {
        return (bool) $this->generateQuery()->update([
            $this->getTokenColumn('expire') => Carbon::parse('now '.$this->getTokenLifetime()),
        ]);
    }

    /**
     * Generate base query used to retrieve user by token
     * and update an existing tokens expire time.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @return mixed
     */
    private function generateQuery()
    {
        return $this->getUserModel()
                    ->select([
                        $this->getUserTable().'.*',
                    ])
                    ->join($this->getTokenTable(), $this->getTokenColumn('user_id'), '=', $this->getUserTable().'.id')
                    ->where($this->getTokenColumn('token'), '=', $this->getToken());
    }

    /**
     * Retrieve user model.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Nodes\Api\Auth\Exceptions\MissingUserModelException
     */
    protected function getUserModel()
    {
        // Make sure a user model is set
        if (empty($this->userModel)) {
            throw new MissingUserModelException('No user model set for API authentication');
        }

        return $this->userModel;
    }

    /**
     * Retrieve user table.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @return string
     */
    protected function getUserTable()
    {
        return $this->getUserModel()->getTable();
    }

    /**
     * Retrieve token from "Authorization" header.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @return string
     */
    protected function getToken()
    {
        return $this->token;
    }

    /**
     * Retrieve token table.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @return string
     */
    protected function getTokenTable()
    {
        return $this->tokenTable;
    }

    /**
     * Retrieve token columns.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @param  string $column
     * @return string
     */
    protected function getTokenColumn($column)
    {
        if (! array_key_exists($column, $this->tokenColumns)) {
            // This should never happen. If it does, then it means
            // that someone is a moron and has removed required
            // settings from the config files. Better safe than sorry.
            throw new BadRequestHttpException;
        }

        return $this->tokenColumns[$column];
    }

    /**
     * Retrieve token lifetime.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @return int
     */
    protected function getTokenLifetime()
    {
        return $this->tokenLifetime;
    }

    /**
     * Check if token has a lifetime.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @return bool
     */
    protected function hasTokenLifetime()
    {
        return ! empty($this->tokenLifetime);
    }
}
