<?php

namespace App\Http\Controllers\Api\Trial;

use App\Http\Resources\TrialUserResource;
use App\Models\User;
use App\Support\Http\Controllers\ApiController;
use App\Support\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class UserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->responseModel(
            $request,
            $request->has('page')
                ? new LengthAwarePaginator(
                $request->has('empty') ? [] : User::factory()->count(10)->make(),
                1000,
                10,
                1
            )
                : ($request->has('id')
                ? ($request->has('empty') ? null : User::factory()->make())
                : ($request->has('empty') ? collect([]) : User::factory()->count(10)->make())),
            TrialUserResource::class,
            ['now' => date_timer()->compound('longDate', ' ', 'longTime')]
        );
    }
}