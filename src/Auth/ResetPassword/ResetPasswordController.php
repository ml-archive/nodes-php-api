<?php

namespace Nodes\Api\Auth\ResetPassword;

use Illuminate\Routing\Controller as IlluminateController;
use Illuminate\Support\Facades\Input;
use Nodes\Api\Auth\Exceptions\MissingUserModelException;
use Nodes\Api\Routing\Helpers as ApiHelpers;
use Nodes\Exceptions\Exception;

/**
 * Class ResetPasswordController.
 */
class ResetPasswordController extends IlluminateController
{
    use ApiHelpers;

    /**
     * Reset password model.
     *
     * @var \Nodes\Api\Auth\ResetPassword\ResetPasswordRepository
     */
    protected $resetPasswordRepository;

    /**
     * Constructor.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  \Nodes\Api\Auth\ResetPassword\ResetPasswordRepository $resetPasswordRepository
     */
    public function __construct(ResetPasswordRepository $resetPasswordRepository)
    {
        $this->resetPasswordRepository = $resetPasswordRepository;

        // Share variables with all views
        view()->share([
            'title' => 'Reset password',
            'senderName' => (config('nodes.api.reset-password.from.name') != 'Nodes') ? config('nodes.api.reset-password.from.name') : config('nodes.project.name'),
        ]);
    }

    /**
     * Reset password form.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string $token
     * @return \Illuminate\View\View
     */
    public function index($token)
    {
        // Validate token
        /** @var $resetToken ResetPasswordModel */
        $resetToken = $this->resetPasswordRepository->getByToken($token);
        if (empty($resetToken) || $resetToken->isUsed()) {
            return view('nodes.api::reset-password.invalid');
        }

        // Check if token's expiry date has been exceed
        if ($resetToken->isExpired()) {
            return view('nodes.api::reset-password.expired');
        }

        return view('nodes.api::reset-password.form', compact('token'));
    }

    /**
     * Reset password confirmation.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return \Illuminate\View\View
     */
    public function done()
    {
        return view('nodes.api::reset-password.done');
    }

    /**
     * Reset/Update user's password.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword()
    {
        // Retrieve received token
        $token = Input::get('token');

        // Validate token
        $resetToken = $this->resetPasswordRepository->getByUnexpiredToken($token);
        if (empty($resetToken)) {
            return view('nodes.api::reset-password.invalid');
        }

        // Retrieve received e-mail
        $email = Input::get('email');

        // Validate e-mail address
        if ($resetToken->email != $email) {
            return redirect()->back()->with(['email' => $email, 'error' => 'Token does not belong to e-mail address']);
        }

        // Retrieve passwords
        $password = Input::get('password');
        $repeatPassword = Input::get('repeat-password');

        // Validate passwords
        if ($password != $repeatPassword) {
            return redirect()->back()->with(['email' => $email, 'error' => 'The two passwords does not match each other']);
        }

        // All good! Update user's password
        $status = $this->resetPasswordRepository->updatePasswordByEmail($email, $password);
        if (empty($status)) {
            return redirect()->back()->with(['email' => $email, 'error' => 'Could not change user\'s password']);
        }

        // Mark token as used
        $resetToken->markAsUsed();

        return redirect()->route('nodes.api.auth.reset-password.done')->with('success', 'Password was successfully changed');
    }

    /**
     * Generate reset password token and send it on e-mail.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return \Nodes\Api\Http\Response
     * @throws \Nodes\Api\Auth\Exceptions\MissingUserModelException
     * @throws \Nodes\Exceptions\Exception
     */
    public function generateResetToken()
    {
        // Retrieve data
        $email = Input::get('email');

        // Validate that an API user model has been specified
        if (empty(config('nodes.api.auth.model'))) {
            throw new MissingUserModelException('No API auth model has been specified');
        }

        // Validate e-mail
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw (new Exception('Missing or invalid e-mail address', 412))->setStatusCode(412);
        }

        // Generate token and send e-mail
        $this->resetPasswordRepository->sendResetPasswordEmail(['email' => $email]);

        return $this->response->content([
            'email' => 'sent',
        ]);
    }
}
