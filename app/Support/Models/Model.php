<?php

namespace App\Support\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    protected $perPage = 10;

    public array $uniques = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        array_unshift($this->uniques, $this->getKeyName());
    }
}
