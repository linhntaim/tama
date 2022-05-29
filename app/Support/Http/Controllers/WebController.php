<?php

namespace App\Support\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

abstract class WebController extends Controller
{
    protected function response(
        Request $request,
        string  $view,
        array   $data = [],
        array   $mergeData = []
    ): View
    {
        return $this->responseView($request, $view, $data, $mergeData);
    }
}
