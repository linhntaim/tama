<?php

namespace App\Http\Controllers\Api\Auth;

use App\Support\Http\Controllers\ApiController;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends ApiController
{
    protected ?string $brokerName = null;

    protected function broker(): PasswordBroker
    {
        return Password::broker($this->brokerName);
    }

    protected function forgotRules(Request $request): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function forgotValidate(Request $request)
    {
        $this->validate($request, $this->forgotRules($request));
    }

    /**
     * @throws ValidationException
     */
    public function forgot(Request $request): JsonResponse
    {
        $this->forgotValidate($request);
        return $this->forgotResponse($request, $this->forgotExecute($request));
    }

    protected function forgotExecute(Request $request): string
    {
        return $this->broker()->sendResetLink([
            'email' => $request->input('email'),
        ]);
    }

    protected function forgotResponse(Request $request, $sent): JsonResponse
    {
        dd($sent);
        switch ($sent) {
            case PasswordBroker::RESET_LINK_SENT:
                return $this->responseSuccess($request);
            case PasswordBroker::RESET_THROTTLED:
                throw new ThrottleRequestsException(trans(PasswordBroker::RESET_THROTTLED));
            default:
                $this->abort404(trans(PasswordBroker::INVALID_USER));
        }
        return $this->responseFail($request);
    }
}
