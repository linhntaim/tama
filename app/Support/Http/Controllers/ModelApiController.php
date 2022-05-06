<?php

namespace App\Support\Http\Controllers;

use App\Support\Models\ModelProvider;

abstract class ModelApiController extends ApiController
{
    public ModelProvider $modelProvider;

    public function __construct()
    {
        take($this->modelProviderClass(), function ($class) {
            $this->modelProvider = new $class;
        });
    }

    protected abstract function modelProviderClass(): string;
}
