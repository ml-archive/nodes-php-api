<?php
namespace Nodes\Api\Auth\EmailVerification;

use Illuminate\Routing\Controller as IlluminateController;
use Illuminate\Support\Facades\Input;
use Nodes\Api\Auth\Exceptions\MissingUserModelException;
use Nodes\Api\Routing\Helpers as ApiHelpers;
use Nodes\Exceptions\Exception;

/**
 * Class EmailVerificationsController
 *
 * @package Nodes\Api\Auth\EmailVerification
 */
class EmailVerificationsController extends IlluminateController
{
    use ApiHelpers;

    /**
     * Reset password model
     *
     * @var \Nodes\Api\Auth\EmailVerification\EmailVerificationRepository
     */
    protected $emailVerificationRepository;

    /**
     * EmailVerificationsController constructor.
     * @param EmailVerificationRepository $emailVerificationRepository
     */
    public function __construct(EmailVerificationRepository $emailVerificationRepository)
    {
        // Gets emailVerificationRepository
        $this->emailVerificationRepository = $emailVerificationRepository;

        // Share variables with all views
        view()->share([
            'title' => 'Email verification',
            'senderName' => (config('nodes.api.email-verification.from.name') != 'Nodes') ? config('nodes.api.email-verification.from.name') : config('nodes.project.name')
        ]);
    }

    /**
     *
     * Verifies user email
     *
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
}