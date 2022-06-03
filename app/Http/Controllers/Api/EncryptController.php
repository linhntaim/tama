<?php

namespace App\Http\Controllers\Api;

use App\Support\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EncryptController extends ApiController
{
    /**
     * @throws ValidationException
     */
    public function encrypt(Request $request): JsonResponse
    {
        $this->validate($request, [
            'data' => 'required|string',
        ]);
        return $this->responseResource($request, [
            'encrypted' => encrypt($request->input('data')),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function decrypt(Request $request): JsonResponse
    {
        $this->validate($request, [
            'data' => 'required|string',
        ]);
        return $this->response($request, [
            'decrypted' => decrypt($request->input('data')),
        ]);
    }
}
