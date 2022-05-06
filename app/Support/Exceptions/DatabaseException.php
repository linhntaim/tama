<?php

namespace App\Support\Exceptions;

use PDOException;
use Throwable;

class DatabaseException extends Exception
{
    public static function from(Throwable $throwable, string|array|null $message = null): static
    {
        return new static($message, $throwable);
    }

    public function __construct(string|array|null $message = null, ?Throwable $previous = null)
    {
        if ($previous instanceof PDOException && empty($message)) {
            $message = $previous->errorInfo[2] ?? $previous->getMessage();
        }

        parent::__construct($message, 500, $previous);
    }
}
