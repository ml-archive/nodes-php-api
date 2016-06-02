<?php
namespace Nodes\Api\Auth\EmailVerification;

use Illuminate\Routing\Controller as IlluminateController;
use Illuminate\Support\Facades\Input;
use Nodes\Api\Auth\Exceptions\MissingUserModelException;
use Nodes\Api\Routing\Helpers as ApiHelpers;
use Nodes\Exceptions\Exception;

/**
 * Class EmailVerificationController
 *
 * @package Nodes\Api\Auth\EmailVerification
 */
class EmailVerificationController extends IlluminateController
{
    use ApiHelpers;

    /**
     * Reset password model
     *
     * @var \Nodes\Api\Auth\EmailVerification\EmailVerificationRepository
     */
    protected $emailVerificationRepository;

    /**
     * EmailVerificationController constructor.
     * @param EmailVerificationRepository $emailVerificationRepository
     */
    public function __construct(EmailVerificationRepository $emailVerificationRepository)
    {
        $this->emailVerificationRepository = $emailVerificationRepository;

        // Share variables with all views
        view()->share([
            'title' => 'Email verification',
            'senderName' => (config('nodes.api.email-verification.from.name') != 'Nodes') ? config('nodes.api.email-verification.from.name') : config('nodes.project.name')
        ]);
    }

    /**
     * @param $token
     * @param $email
     * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @author Paulius Navickas <pana@nodes.dk>
     */
    public function index($token, $email)
    {
        // Validate token
        $verificationToken = $this->emailVerificationRepository->getByUnexpiredToken($token);

        if (empty($verificationToken)) {
            return view('nodes.api::email-verification.invalid');
        }

        // Validate e-mail address
        if ($verificationToken->email != $email) {
            return view('nodes.api::email-verification.invalid');
        }

        // All good! Update user's password
        $status = $this->emailVerificationRepository->updateVerificationByEmail($email);
        if (empty($status)) {
            return view('nodes.api::email-verification.invalid')->with(['email' => $email, 'error' => 'Couldn\'t verify the user. Please contact administrator']);
        }

        // Mark token as used
        $verificationToken->markAsUsed();

        return view('nodes.api::email-verification.done')->with('success', 'User was successfully verified');
    }

    /**
     * Generate reset password token and send it on e-mail
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return \Nodes\Api\Http\Response
     * @throws \Nodes\Api\Auth\Exceptions\MissingUserModelException
     * @throws \Nodes\Exceptions\Exception
     */
    public function generateEmailVerificationToken()
    {
        // Retrieve data
        $email = Input::get('email');

        // Validate that an API user model has been specified
        if (empty(config('nodes.api.auth.model'))) {
            throw new MissingUserModelException('No API auth model has been specified');
        }

        // Validate e-mail
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw (new Exception('Missing or invalid e-mail address', 412))->setStatusCode(412);
        }

        // Generate token and send e-mail
        $status = $this->resetPasswordRepository->sendResetPasswordEmail(['email' => $email]);
        if (empty($status)) {
            throw new Exception('Could not send reset password e-mail', 500);
        }

        return $this->response->content([
            'email' => 'sent'
        ]);
    }
}