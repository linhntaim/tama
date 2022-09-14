<?php

namespace App\Support\Http\Responses;

use App\Support\Http\Concerns\Requests;
use App\Support\Http\Concerns\Responses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Responses\FailedPasswordResetLinkRequestResponse as BaseFailedPasswordResetLinkRequestResponse;

class FailedPasswordResetLinkRequestResponse extends BaseFailedPasswordResetLinkRequestResponse
{
    use Requests, Responses;

    /**
     * @throws ValidationException
     */
    public function toResponse($request): RedirectResponse
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
