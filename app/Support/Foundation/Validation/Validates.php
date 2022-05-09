<?php

namespace App\Support\Foundation\Validation;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

trait Validates
{
    private function withAfterCallback(Validator $validator, callable|string $callback = null): Validator
    {
        return is_null($callback) ? $validator : $validator->after($callback);
    }

    /**
     * Run the validation routine against the given validator.
     *
     * @throws ValidationException
     */
    public function validateDataWith(
        Validator|array $validator,
        array           $data,
        callable|string $afterCallback = null
    ): array
    {
        if (is_array($validator)) {
            $validator = $this->getValidationFactory()->make($data, $validator);
        }

        return $this->withAfterCallback($validator, $afterCallback)->validate();
    }

    /**
     * Validate the given request with the given rules.
     *
     * @throws ValidationException
     */
    public function validateData(
        array           $data,
        array           $rules,
        array           $messages = [],
        array           $customAttributes = [],
        callable|string $afterCallback = null
    ): array
    {
        return $this->withAfterCallback(
            $this->getValidationFactory()->make(
                $data, $rules, $messages, $customAttributes
            ),
            $afterCallback
        )->validate();
    }

    /**
     * Validate the given request with the given rules.
     *
     * @throws ValidationException
     */
    public function validateDataWithBag(
        string          $errorBag,
        array           $data,
        array           $rules,
        array           $messages = [],
        array           $customAttributes = [],
        callable|string $afterCallback = null
    ): array
    {
        try {
            return $this->validateData($data, $rules, $messages, $customAttributes, $afterCallback);
        }
        catch (ValidationException $e) {
            $e->errorBag = $errorBag;

            throw $e;
        }
    }

    /**
     * Get a validation factory instance.
     *
     * @return ValidationFactory
     */
    protected function getValidationFactory(): ValidationFactory
    {
        return app(ValidationFactory::class);
    }
}
