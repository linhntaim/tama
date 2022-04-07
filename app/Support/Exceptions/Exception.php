<?php

/**
 * Base
 */

namespace App\Support\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Exception extends \Exception implements HttpExceptionInterface
{
    public static function from(Throwable $throwable): static
    {
        return new static(null, 0, $throwable);
    }

    /**
     * @var array|string[]
     */
    protected array $messages;

    protected ?array $data;

    protected ?array $headers;

    public function __construct(string|array|null $message = null, int|string $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('', $code, $previous);

        if (empty($message)) { // null, empty string, empty array
            if (is_null($previous)) {
                $this->message = '';
                $this->messages = [''];
            }
            else {
                $this->message = $previous->getMessage();
                $this->messages = $previous instanceof Exception ?
                    $previous->getMessages() : [$this->getMessage()];
            }
        }
        elseif (is_string($message)) {
            $this->message = $message;
            $this->messages = [$message];
        }
        elseif (is_array($message)) {
            $this->message = $message[0];
            $this->messages = $message;
        }

        if (!is_null($previous)) {
            $this->file = $previous->getFile();
            $this->line = $previous->getLine();
        }
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getStatusCode(): int
    {
        return is_int($this->code) && $this->code >= 100 && $this->code < 600 ? $this->code : 500;
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getData(): array
    {
        return $this->data ?? [];
    }

    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers ?? [];
    }
}