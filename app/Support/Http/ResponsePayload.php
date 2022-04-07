<?php

namespace App\Support\Http;

use App\Support\Exceptions\Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ResponsePayload implements Arrayable
{
    public static function create(bool|string|array|Throwable|null $source = null): static
    {
        return new static($source);
    }

    protected array $headers = [];

    protected bool|null $status = null;

    protected int|null $statusCode = null;

    protected int|string|null $errorCode = null;

    protected ?Throwable $throwable = null;

    /**
     * @var array|string[]|null
     */
    protected ?array $messages = null;

    protected ?array $data = null;

    public function __construct(bool|string|array|Throwable|null $source = null)
    {
        if (is_bool($source)) {
            $this->setStatus($source);
        }
        elseif (is_string($source)) {
            $this->setStatus(false)
                ->setMessages($source);
        }
        elseif (is_array($source)) {
            $this->setStatus(true)
                ->setData($source);
        }
        elseif ($source instanceof Throwable) {
            $this->setException($source);
        }
    }

    public function setHeaders(array $headers, bool $fresh = false): static
    {
        if ($fresh) {
            $this->headers = $headers;
        }
        else {
            $this->headers += $headers;
        }
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): bool
    {
        return $this->status ?? true;
    }

    public function setStatusCode(?int $statusCode): static
    {
        if (!is_null($statusCode)) {
            $this->statusCode = $statusCode;
        }
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode ?? ($this->getStatus() ? 200 : 500);
    }

    public function setErrorCode(int|string|null $errorCode): static
    {
        if (!is_null($errorCode)) {
            $this->errorCode = $errorCode;
        }
        return $this;
    }

    public function getErrorCode(): int|string|null
    {
        return $this->errorCode;
    }

    public function setException(Throwable $throwable): static
    {
        $this->throwable = $throwable;
        if ($throwable instanceof HttpExceptionInterface) {
            $this->setHeaders($throwable->getHeaders());
        }
        if (is_null($this->status)) {
            $this->setStatus(false);
        }
        if (is_null($this->statusCode)) {
            if ($throwable instanceof ValidationException) {
                $this->setStatusCode($throwable->status);
            }
            elseif ($throwable instanceof AuthenticationException) {
                $this->setStatusCode(401);
            }
            elseif ($throwable instanceof HttpExceptionInterface) {
                $this->setStatusCode($throwable->getStatusCode());
            }
            else {
                $this->setStatusCode(500);
            }
        }
        if (is_null($this->errorCode)) {
            $this->setErrorCode($throwable->getCode());
        }
        if (is_null($this->messages)) {
            if ($throwable instanceof ValidationException) {
                $this->setMessages($throwable->validator->errors()->all());
            }
            elseif ($throwable instanceof Exception) {
                $this->setMessages($throwable->getMessages());
            }
            else {
                $this->setMessages($throwable->getMessage());
            }
        }
        if (is_null($this->data)) {
            if ($throwable instanceof ValidationException) {
                $this->setData([
                    'validation' => $throwable->errors(),
                ]);
            }
            elseif ($throwable instanceof Exception) {
                if (count($data = $throwable->getData())) {
                    $this->setData($data);
                }
            }
        }
        return $this;
    }

    public function getException(bool $debug = false): ?Throwable
    {
        return config('app.debug') || $debug ? $this->throwable : null;
    }

    public function getExceptionAsArray(bool $debug = false): ?array
    {
        if (is_null($this->throwable) || (!config('app.debug') && !$debug)) {
            return null;
        }
        $exceptions = [];
        $exception = $this->throwable;
        do {
            $exceptions[] = [
                'class' => get_debug_type($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace(),
            ];
        }
        while ($exception = $exception->getPrevious());
        return $exceptions;
    }

    public function setMessages(array|string|null $messages, bool $fresh = false): static
    {
        if (!is_null($messages)) {
            if ($fresh) {
                $this->messages = (array)$messages;
            }
            else {
                $this->messages = array_merge($this->messages ?? [], (array)$messages);
            }
        }
        return $this;
    }

    public function getMessages(): ?array
    {
        return $this->messages;
    }

    public function setData(?array $data, bool $fresh = false): static
    {
        if (!is_null($data)) {
            if ($fresh) {
                $this->data = $data;
            }
            else {
                $this->data = array_merge($this->data ?? [], $data);
            }
        }
        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function toArray(bool $debug = false): array
    {
        return [
            '_status' => $this->getStatus(),
            '_status_code' => $this->getStatusCode(),
            '_error_code' => $this->getErrorCode(),
            '_exception' => $this->getExceptionAsArray($debug),
            '_messages' => $this->getMessages(),
            '_data' => $this->getData(),
        ];
    }
}