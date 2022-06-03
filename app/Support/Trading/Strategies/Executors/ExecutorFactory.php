<?php

namespace App\Support\Trading\Strategies\Executors;

use App\Support\Trading\Strategies\Model\Strategy;
use InvalidArgumentException;

class ExecutorFactory
{
    public static array $executors = [
        CustomExecutor::NAME => CustomExecutor::class,
    ];

    public static function create(Strategy $strategy): Executor
    {
        if (is_null($class = (self::$executors[$strategy->executor] ?? null))) {
            throw new InvalidArgumentException(sprintf('Executor [%s] was not supported.', $strategy->executor));
        }
        return new $class($strategy);
    }
}
