<?php

namespace App\Support\Http\Controllers;

use App\Support\Foundation\Validation\ValidatesRequests;
use App\Support\Http\Concerns\Abort;
use App\Support\Http\Concerns\Requests;
use App\Support\Http\Concerns\Responses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Requests, Responses, Abort;
}
