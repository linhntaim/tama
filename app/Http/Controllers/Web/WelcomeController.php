<?php

namespace App\Http\Controllers\Web;

use App\Support\Http\Controllers\WebController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WelcomeController extends WebController
{
    public function index(Request $request, $path = null): View
    {
        if (!is_null($path)) {
            $this->abort404();
        }
        return $this->response($request, 'welcome');
    }
}
