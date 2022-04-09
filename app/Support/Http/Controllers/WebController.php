<?php

namespace App\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class WebController extends Controller
{
    protected function response(string $view, array $data = [], array $mergeData = []): View
    {
        return $this->responseView($view, $data, $mergeData);
    }
}