<?php

namespace App\Http\Controllers\Web;

use App\Support\Http\Controllers\WebController;
use Illuminate\Contracts\View\View;

class WelcomeController extends WebController
{
    public function index(): View
    {
        return $this->response('welcome');
    }
}