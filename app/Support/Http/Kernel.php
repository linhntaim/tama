<?php

namespace App\Support\Http;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * @throws BindingResolutionException
     */
    public function bootstrap()
    {
        $this->app->instance('advanced_request', new AdvancedRequest($this->app->make('request')));
        parent::bootstrap();
    }
}
