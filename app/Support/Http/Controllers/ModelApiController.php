<?php

namespace App\Support\Http\Controllers;

use App\Support\Http\Controllers\Concerns\HasModelApi;

abstract class ModelApiController extends ApiController
{
    use HasModelApi;
}
