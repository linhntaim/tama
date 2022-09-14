<?php

namespace App\Support\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Serialize implements CastsAttributes
{
    protected array|bool $allowedClasses;

    public function __construct(string|bool $allowedClasses = true)
    {
        $this->allowedClasses = is_bool($allowedClasses) ? $allowedClasses : explode(',', $allowedClasses);
    }

    public function get($model, string $key, $value, array $attributes)
    {
        return is_null($value) ? null : safe_unserialize($value, $this->allowedClasses);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return is_null($value) ? null : serialize($value);
    }
}
