<?php

namespace App\Support\Http\Controllers;

use App\Support\Foundation\Validation\ValidatesRequests;
use App\Support\Http\Responses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Responses;
}
