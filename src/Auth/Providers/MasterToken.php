<?php
namespace Nodes\Api\Auth\Providers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Route;
use Dingo\Api\Contract\Auth\Provider as DingoAuthContract;
use Nodes\Api\Auth\Exceptions\InvalidTokenException;
use Nodes\Api\Auth\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class MasterToken
 *
 * @package Nodes\Api\Auth\Providers
 */
class MasterToken implements DingoAuthContract
{
    /**
     * User model
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $userModel;

    /**
     * Wether or not Master Token is enabled
     *
     * @var boolean
     */
    protected $enabled;

    /**
     * Token from "X-Master-Token" header
     *
     * @var string
     */
    protected $token;

    /**
     * Token salt
     *
     * @var string
     */
    protected $tokenSalt;

    /**
     * Token columns used in condition
     *
     * @var array
     */
    protected $tokenColumns = [];

    /**
     * Auth constructor
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @access public
     */
    public function __construct()
    {
        // Set user model
        $this->userModel = prepare_config_instance(config('nodes.api.auth.model', null));

        // Set token table
        $this->setTokenSettings();
    }

    /**
     * Set token settings
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @access protected
     * @return \Nodes\Api\Auth\Providers\MasterToken
     */
    protected function setTokenSettings()
    {
        $this->enabled = (bool) config('nodes.auth.masterToken.enabled', false);

        // Salt used to generate the unique master token
        $this->tokenSalt = config('nodes.auth.masterToken.salt', 'nodes+' . env('APP_ENV'));

        // Fields used to retrieve user associated with master token
        $this->tokenColumns = config('nodes.auth.masterToken.user', [
            'column'   => 'master',
            'operator' => '=',
            'value'    => 1,
        ]);

        return $this;
    }

    /**
     * Authenticate by token
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @access public
     * @param  \Illuminate\Http\Request $request
     * @param  \Dingo\Api\Routing\Route $route
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Nodes\Api\Auth\Exceptions\InvalidTokenException
     * @throws \Nodes\Api\Auth\Exceptions\UnauthorizedException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function authenticate(Request $request, Route $route)
    {
        // Validate "X-Master-Token" header
        if (empty($request->header('x-master-token'))) {
            throw new BadRequestHttpException;
        }

        // Make sure Master Token authorization is enabled
        if ($this->isEnabled()) {
            throw new UnauthorizedException('Authorization by master token is not available');
        }

        // Set token from "X-Master-Token" header
        $this->token = (string) $request->header('x-master-token');

        // Validate master token
        if (!$this->validateMasterToken()) {
            throw new InvalidTokenException('Invalid master token provided');
        }

        $user = $this->getUserByToken();
        if (empty($user)) {
            throw new UnauthorizedException('No user associated with master token');
        }

        return $user;
    }

    /**
     * Retrieve user by token
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @access protected
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getUserByToken()
    {
        // Look up in cache and return if found
        if ($user = cache_get('api.masterToken', $this->getCacheParams())) {
            return $user;
        }

        $user = $this->getUserModel()->where(
            $this->getTokenColumn('column'),
            $this->getTokenColumn('operator'),
            $this->getTokenColumn('value')
        )
                     ->first();

        // Add to cache
        cache_put('api.masterToken', $this->getCacheParams(), $user);

        return $user;
    }

    /**
     * getCacheParams
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
     * Validate master token
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @access protected
     * @return boolean
     */
    protected function validateMasterToken()
    {
        return in_array($this->token, $this->generateMasterToken());
    }

    /**
     * Generate master token
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @date   03-11-2015
     * @access protected
     * @return array
     */
    protected function generateMasterToken()
    {
        return [
            hash('sha256', $this->tokenSalt . '+' . config('app.key') . '+' . Carbon::now()->toDateString()),
            hash('sha256', $this->tokenSalt . '+' . config('app.key') . '+' . Carbon::now()->addDay()->toDateString()),
            hash('sha256', $this->tokenSalt . '+' . config('app.key') . '+' . Carbon::now()->subDay()->toDateString()),
        ];
    }

    /**
     * Wether or not master token is enabled
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @access protected
     * @return boolean
     */
    protected function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Retrieve user model
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @access protected
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getUserModel()
    {
        return $this->userModel;
    }

    /**
     * Retrieve token from "Authorization" header
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @access protected
     * @return string
     */
    protected function getToken()
    {
        return $this->token;
    }

    /**
     * Retrieve token columns
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @access protected
     * @param  string $column
     * @return string
     */
    protected function getTokenColumn($column)
    {
        if (!array_key_exists($column, $this->tokenColumns)) {
            // This should never happen. If it does, then it means
            // that someone is a moron and has removed required
            // settings from the config files. Better safe than sorry.
            throw new BadRequestHttpException;
        }

        return $this->tokenColumns[$column];
    }
}