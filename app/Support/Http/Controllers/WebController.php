<?php

namespace App\Support\Http\Controllers;

use App\Support\Http\Request;
use Illuminate\Contracts\View\View;

class WebController extends Controller
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