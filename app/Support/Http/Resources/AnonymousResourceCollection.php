<?php

namespace App\Support\Http\Resources;

class AnonymousResourceCollection extends JsonResourceCollection
{
    public function __construct($resource, $collects)
    {
        $this->collects = $collects;

        parent::__construct($resource);
    }
}