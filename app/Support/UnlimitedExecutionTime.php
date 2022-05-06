<?php

namespace App\Support;

use Closure;

trait UnlimitedExecutionTime
{
    protected bool $unlimitedExecutionTime = false;

    protected function unlimitedExecutionTime(Closure $callback, mixed ...$args): mixed
    {
        return with_unlimited_execution_time_if($this->unlimitedExecutionTime, $callback, ...$args);
    }
}
