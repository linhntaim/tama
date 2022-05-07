<?php

namespace App\Support\Foundation\Bus;

use Illuminate\Foundation\Bus\Dispatchable as BaseDispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;

trait Dispatchable
{
    use BaseDispatchable;

    public static function dispatchWith($callback, ...$arguments): PendingDispatch
    {
        return new PendingDispatch(with(new static(...$arguments), $callback));
    }
}
