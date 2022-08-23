<?php

namespace App\Support\Concerns;

use Closure;

trait UnlimitedResource
{
    use UnlimitedExecutionTime, UnlimitedMemoryUsage;

    protected function unlimitedResource(Closure $callback, mixed ...$args): mixed
    {
        return $this->unlimitedExecutionTime(function () use ($callback, $args) {
            return $this->unlimitedMemoryUsage($callback, ...$args);
        });
    }
}
