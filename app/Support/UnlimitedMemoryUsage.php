<?php

namespace App\Support;

use Closure;

trait UnlimitedMemoryUsage
{
    protected bool $unlimitedMemoryUsage = false;

    protected function unlimitedMemoryUsage(Closure $callback, mixed ...$args): mixed
    {
        return with_unlimited_memory_usage_if($this->unlimitedMemoryUsage, $callback, ...$args);
    }
}
