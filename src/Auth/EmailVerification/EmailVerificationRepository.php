<?php

namespace Nodes\Api\Auth\EmailVerification;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Support\Facades\Mail;
use Nodes\Api\Auth\Exceptions\ResetPasswordNoUserException;
use Nodes\Database\Eloquent\Repository as NodesRepository;
use Nodes\Api\Auth\Exceptions\MissingUserModelException;

/**
 * Class EmailVerificationRepository.
 */
class EmailVerificationRepository extends NodesRepository
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
     * @author Paulius Navickas <pana@nodes.dk>
     *
     * @param  \Nodes\Api\Auth\EmailVerification\EmailVerificationModel $model
     * @throws \Nodes\Api\Auth\Exceptions\MissingUserModelException
     */
    public function __construct(EmailVerificationModel $model)
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
     * @author Paulius Navickas <pana@nodes.dk>
     *
     * @param  string $token
     * @return \Nodes\Api\Auth\EmailVerification\EmailVerificationModel
     */
    public function getByToken($token)
    {
        return $this->where('token', '=', $token)->first();
    }

    /**
     * Retrieve by unexpired token.
     *
     * @author Paulius Navickas <pana@nodes.dk>
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
     * Generate and send a email with link instructions.
     *
     * @author Paulius Navickas <pana@nodes.dk>
     *
     * @param  \Illuminate\Database\Eloquent\Model $user
     * @return bool
     * @throws \Nodes\Api\Auth\Exceptions\ResetPasswordNoUserException
     */
    public function sendVerificationEmail(IlluminateModel $user)
    {
        if (empty($user)) {
            throw new ResetPasswordNoUserException('Could not find any user with those credentials');
        }

        // Generate email verification token
        $token = $this->generateEmailVerificationToken($user);

        // Generate email verification domain
        $domain = env('NODES_ENV', false) ? sprintf('https://%s.%s', env('APP_NAME'), env('APP_DOMAIN')) : config('app.url');

        // Send e-mail with instructions on how to reset password
        $status = (bool) Mail::send([
            'html' => config('nodes.api.email-verification.views.html', 'nodes.api::email-verification.emails.html'),
            'text' => config('nodes.api.email-verification.views.text', 'nodes.api::email-verification.emails.text'),
        ], [
            'user' => $user,
            'domain' => $domain,
            'email' => $user->email,
            'token' => $token,
            'expire' => config('nodes.api.email-verification.expire'),
            'senderName' => (config('nodes.api.email-verification.from.name') != 'Nodes') ? config('nodes.api.email-verification.from.name') : config('nodes.project.name'),
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->from(config('nodes.api.email-verification.from.email', 'no-reply@nodes.dk'), config('nodes.api.email-verification.from.name', 'Nodes'))
                ->subject(config('nodes.api.email-verification.subject', 'Email verification request'));
        });

        return $status;
    }

    /**
     * Generate verification token.
     *
     * @author Paulius Navickas <pana@nodes.dk>
     *
     * @param  \Illuminate\Database\Eloquent\Model $user
     * @return string
     */
    protected function generateEmailVerificationToken(IlluminateModel $user)
    {
        // Generate new token using Laravel's encryption key
        $token = hash_hmac('sha256', str_random(40), config('app.key'));

        // Expire timestamp
        $expire = Carbon::now()->addDays(config('nodes.api.email-verification.expire', 1));

        // If user has previously tried to reset his/her password
        // we should just update the token of the previous entry
        // instead of creating a new one
        $verificationToken = $this->where('email', '=', $user->email)->first();
        if (! empty($verificationToken)) {
            $verificationToken->update(['token' => $token, 'used' => 0, 'expire_at' => $expire->format('Y-m-d H:i:s')]);
        } else {
            $this->create(['email' => $user->email, 'token' => $token, 'expire_at' => $expire->format('Y-m-d H:i:s')]);
        }

        return $token;
    }

    /**
     * Update e-mail verification record by e-mail.
     *
     * @author Paulius Navickas <pana@nodes.dk>
     *
     * @param  string $email
     * @return bool
     */
    public function updateVerificationByEmail($email)
    {
        // Retrieve user by e-mail
        $user = $this->authModel->where('email', '=', $email)->first();
        if (empty($user)) {
            return false;
        }

        // Update user with a timestamp of when he/her was verified
        return (bool) $user->fill([
            'is_verified' => 1,
            'verified_at' => Carbon::now(),
        ])->save();
    }
}
