<?php

namespace App\Support\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel implements IModel
{
    use ModelTrait;
}