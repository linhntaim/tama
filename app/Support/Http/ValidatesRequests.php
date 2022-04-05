<?php

namespace App\Support\Http;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

trait ValidatesRequests
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
    public function validateWith(
        Validator|array $validator,
        Request         $request = null,
        callable|string $afterCallback = null
    ): array
    {
        $request = $request ?: request();

        if (is_array($validator)) {
            $validator = $this->getValidationFactory()->make($request->all(), $validator);
        }

        return $this->withAfterCallback($validator, $afterCallback)->validate();
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
        return $this->withAfterCallback(
            $this->getValidationFactory()->make(
                $request->all(), $rules, $messages, $customAttributes
            ),
            $afterCallback
        )->validate();
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
        try {
            return $this->validate($request, $rules, $messages, $customAttributes, $afterCallback);
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