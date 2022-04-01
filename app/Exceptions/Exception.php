<?php

/**
 * Base
 */

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Exception extends \Exception implements HttpExceptionInterface
{
    public static function from(Throwable $throwable): static
    {
        return new static('', 0, $throwable);
    }

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if (!is_null($previous)) {
            if (empty($message)) {
                $this->message = $previous->getMessage();
            }
            $this->file = $previous->getFile();
            $this->line = $previous->getLine();
        }
    }

    public function getStatusCode(): int
    {
        return $this->code >= 100 && $this->code < 600 ? $this->code : 500;
    }

    public function getHeaders(): array
    {
        return [];
    }
}