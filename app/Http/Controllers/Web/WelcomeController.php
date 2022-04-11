<?php

namespace App\Http\Controllers\Web;

use App\Support\Http\Controllers\WebController;
use App\Support\Http\Request;
use Illuminate\Contracts\View\View;

class WelcomeController extends WebController
{
    public function index(Request $request): View
    {
        return $this->response($request, 'welcome');
    }
}