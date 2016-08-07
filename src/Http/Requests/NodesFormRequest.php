<?php

namespace Nodes\Api\Http\Requests;

use Dingo\Api\Http\Request;
use Dingo\Api\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Nodes\Validation\Exceptions\ValidationException;

/**
 * Class NodesApiRequest
 */
class NodesFormRequest extends FormRequest
{
    /**
     * @var array
     */
    protected $errorCodes = [];

    /**
     * Retrieve errorCodes
     *
     * @author Pedro Coutinho <peco@nodesagency.com>
     * @access public
     * @return array
     */
    public function getErrorCodes()
    {
        return $this->errorCodes;
    }

    /**
     * Set errorCodes
     *
     * @author Pedro Coutinho <peco@nodesagency.com>
     * @access public
     *
     * @param  array $errorCodes
     *
     * @return NodesFormRequest
     */
    public function setErrorCodes(array $errorCodes)
    {
        $this->errorCodes = $errorCodes;

        return $this;
    }

    /**
     * failedValidation
     *
     * @author Pedro Coutinho <peco@nodesagency.com>
     * @access protected
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return void
     * @throws \Nodes\Validation\Exceptions\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->container['request'] instanceof Request) {
            throw new ValidationException($validator, $this->getErrorCodes());
        }

        parent::failedValidation($validator);
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @author Pedro Coutinho <peco@nodesagency.com>
     * @access public
     *
     * @param array $errors
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function response(array $errors)
    {
        if (($this->ajax() && ! $this->pjax()) || $this->wantsJson()) {
            return new JsonResponse($errors, ValidationException::VALIDATION_FAILED);
        }

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            // This makes errors display properly (original errors are placed under the key 'errors' instead of 'error')
            ->with('error', $this->getValidatorInstance()->getMessageBag());
    }
}