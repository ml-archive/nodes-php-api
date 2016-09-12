<?php

namespace Nodes\Api\Auth\ResetPassword;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Support\Facades\Mail;
use Nodes\Api\Auth\Exceptions\ResetPasswordNoUserException;
use Nodes\Database\Eloquent\Repository as NodesRepository;
use Nodes\Api\Auth\Exceptions\MissingUserModelException;

/**
 * Class ResetPasswordRepository.
 */
class ResetPasswordRepository extends NodesRepository
{
    /**
     * API auth model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $authModel;

    /**
     * Constructor.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  \Nodes\Api\Auth\ResetPassword\ResetPasswordModel $model
     * @throws \Nodes\Api\Auth\Exceptions\MissingUserModelException
     */
    public function __construct(ResetPasswordModel $model)
    {
        $this->setupRepository($model);

        // Retrieve namespace of API auth model
        $userModel = config('nodes.api.auth.model', null);
        if (empty($userModel)) {
            throw new MissingUserModelException('No API auth model has been specified');
        }

        // Instantiate auth model
        $this->authModel = app($userModel);
    }

    /**
     * Retrieve by token.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string $token
     * @return \Nodes\Api\Auth\ResetPassword\Model
     */
    public function getByToken($token)
    {
        return $this->where('token', '=', $token)
                    ->first();
    }

    /**
     * Retrieve by unexpired token.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string $token
     * @return \Nodes\Api\Auth\ResetPassword\ResetPasswordModel
     */
    public function getByUnexpiredToken($token)
    {
        return $this->where('token', '=', $token)
                    ->where('expire_at', '>', Carbon::now()->format('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Generate and send a email with reset password instructions.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  array $conditions WHERE conditions to locate user. Format: ['column' => 'value']
     * @throws \Nodes\Api\Auth\Exceptions\ResetPasswordNoUserException
     */
    public function sendResetPasswordEmail(array $conditions)
    {
        // Validate conditions
        if (empty($conditions)) {
            throw new ResetPasswordNoUserException('Conditions can\'t be empty');
        }

        // Add conditions to query builder
        foreach ($conditions as $column => $value) {
            $this->authModel = $this->authModel->where($column, '=', $value);
        }

        // Retrieve user with conditions
        $user = $this->authModel->first();
        if (empty($user)) {
            throw new ResetPasswordNoUserException('Could not find any user with those credentials');
        }

        // Generate reset password token
        $token = $this->generateResetPasswordToken($user);

        // Generate reset password domain
        $domain = env('NODES_ENV', false) ? sprintf('https://%s.%s', env('APP_NAME'), env('APP_DOMAIN')) : config('app.url');

        // Send e-mail with instructions on how to reset password
        Mail::send([
            'html' => config('nodes.api.reset-password.views.html', 'nodes.api::reset-password.emails.html'),
            'text' => config('nodes.api.reset-password.views.text', 'nodes.api::reset-password.emails.text'),
        ], [
            'user' => $user,
            'domain' => $domain,
            'token' => $token,
            'expire' => config('nodes.api.reset-password.expire'),
            'senderName' => (config('nodes.api.reset-password.from.name') != 'Nodes') ? config('nodes.api.reset-password.from.name') : config('nodes.project.name'),
        ], function ($message) use ($user) {
            $message->to($user->email)
                    ->from(config('nodes.api.reset-password.from.email', 'no-reply@nodes.dk'), config('nodes.api.reset-password.from.name', 'Nodes'))
                    ->subject(config('nodes.api.reset-password.subject', 'Reset password request'));
        });
    }

    /**
     * Generate reset token.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  \Illuminate\Database\Eloquent\Model $user
     * @return string
     */
    protected function generateResetPasswordToken(IlluminateModel $user)
    {
        // Generate new token using Laravel's encryption key
        $token = hash_hmac('sha256', str_random(40), config('app.key'));

        // Expire timestamp
        $expire = Carbon::now()->addMinutes(config('nodes.api.reset-password.expire', 60));

        // If user has previously tried to reset his/her password
        // we should just update the token of the previous entry
        // instead of creating a new one
        $resetToken = $this->where('email', '=', $user->email)->first();
        if (! empty($resetToken)) {
            $resetToken->update(['token' => $token, 'used' => 0, 'expire_at' => $expire->format('Y-m-d H:i:s')]);
        } else {
            $this->insert(['email' => $user->email, 'token' => $token, 'expire_at' => $expire->format('Y-m-d H:i:s')]);
        }

        return $token;
    }

    /**
     * Update user's password by e-mail.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string $email
     * @param  string $password
     * @return bool
     */
    public function updatePasswordByEmail($email, $password)
    {
        // Retrieve user by e-mail
        $user = $this->authModel->where('email', '=', $email)->first();
        if (empty($user)) {
            return false;
        }

        // Update user with new password
        return (bool) $user->update([
            'password' => $password,
        ]);
    }
}
