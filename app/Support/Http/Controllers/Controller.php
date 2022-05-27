<?php

namespace App\Support\Http\Controllers;

use App\Support\Abort;
use App\Support\Foundation\Validation\ValidatesRequests;
use App\Support\Http\Requests;
use App\Support\Http\Responses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use Requests, AuthorizesRequests, DispatchesJobs, ValidatesRequests, Responses, Abort;
}
