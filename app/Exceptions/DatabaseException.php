<?php

/**
 * Base
 */

namespace App\Exceptions;

use PDOException;
use Throwable;

class DatabaseException extends Exception
{
    public static function from(Throwable $throwable): static
    {
        return new static('', $throwable);
    }

    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        if ($previous instanceof PDOException && empty($message)) {
            $message = $previous->errorInfo[2] ?? $previous->getMessage();
        }

        parent::__construct($message, 500, $previous);
    }
}