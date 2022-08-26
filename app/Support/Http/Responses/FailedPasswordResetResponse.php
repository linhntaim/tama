<?php

namespace App\Support\Http\Responses;

use App\Support\Http\Concerns\Requests;
use App\Support\Http\Concerns\Responses;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Responses\FailedPasswordResetResponse as BaseFailedPasswordResetResponse;

class FailedPasswordResetResponse extends BaseFailedPasswordResetResponse
{
    use Requests, Responses;

    /**
     * @throws ValidationException
     */
    public function toResponse($request)
    {
        if ($this->advancedRequest()->expectsJson()) {
            throw ValidationException::withMessages([
                'email' => [trans($this->status)],
            ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($this->status)]);
    }
}
