<?php

namespace App\Support\Foundation\Validation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidatesRequests
{
    use Validates;

    /**
     * Run the validation routine against the given validator.
     *
     * @throws ValidationException
     */
    public function validateWith(
        Validator|array $validator,
        Request         $request = null,
        callable|string $afterCallback = null
    ): array
    {
        return $this->validateDataWith($validator, ($request ?: request())->all(), $afterCallback);
    }

    /**
     * Validate the given request with the given rules.
     *
     * @throws ValidationException
     */
    public function validate(
        Request         $request,
        array           $rules,
        array           $messages = [],
        array           $customAttributes = [],
        callable|string $afterCallback = null
    ): array
    {
        return $this->validateData(
            $request->all(),
            $rules,
            $messages,
            $customAttributes,
            $afterCallback
        );
    }

    /**
     * Validate the given request with the given rules.
     *
     * @throws ValidationException
     */
    public function validateWithBag(
        string          $errorBag,
        Request         $request,
        array           $rules,
        array           $messages = [],
        array           $customAttributes = [],
        callable|string $afterCallback = null
    ): array
    {
        return $this->validateDataWithBag(
            $errorBag,
            $request->all(),
            $rules,
            $messages,
            $customAttributes,
            $afterCallback
        );
    }
}
