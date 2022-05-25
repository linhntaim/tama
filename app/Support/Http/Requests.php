<?php

namespace App\Support\Http;

use Illuminate\Http\Request;

trait Requests
{
    protected Request $request;

    protected AdvancedRequest $advancedRequest;

    protected function request(): Request
    {
        return $this->request ?? ($this->request = app('request'));
    }

    protected function advancedRequest(): AdvancedRequest
    {
        return $this->advancedRequest ?? ($this->advancedRequest = app('advanced_request'));
    }
}
