<?php

namespace App\Support\Exceptions;

use BadMethodCallException;
use Throwable;

class FileException extends Exception
{
    public static function from(Throwable $throwable, array|string|null $message = null): static
    {
        throw new BadMethodCallException('Method not supported.');
    }
}