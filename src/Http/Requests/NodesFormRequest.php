<?php

namespace Nodes\Api\Http\Requests;

use Nodes\Validation\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Dingo\Api\Http\FormRequest;
use Illuminate\Http\Request;

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
     * @return array|null
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
     * @param  array $errorCodes
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
     * @param \Illuminate\Contracts\Validation\Validator $validator
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
}