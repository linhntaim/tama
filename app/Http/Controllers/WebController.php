<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class WebController extends Controller
{
    protected function response(string $view, array $data = [], array $mergeData = []): View
    {
        return $this->responseView($view, $data, $mergeData);
    }
}