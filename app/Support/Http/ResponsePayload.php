<?php

namespace App\Support\Http;

use App\Exceptions\Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ResponsePayload implements Arrayable
{
    public static function create(array|Throwable|null $source = null): static
    {
        return new static($source);
    }

    protected bool|null $status = null;

    /**
     * @var array|string[]|null
     */
    protected ?array $messages = null;

    protected ?array $data = null;

    protected ?Throwable $throwable = null;

    protected int|null $statusCode = null;

    protected int|string|null $errorCode = null;

    protected array $headers = [];

    public function __construct(array|Throwable|null $source = null)
    {
        if (is_array($source)) {
            $this->setStatus(true)
                ->setData($source)
                ->setStatusCode(200);
        }
        elseif ($source instanceof Throwable) {
            $this->setThrowable($source);
        }
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function setMessages(array|string $messages): static
    {
        $this->messages = (array)$messages;
        return $this;
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function setThrowable(Throwable $throwable): static
    {
        if (is_null($this->status)) {
            $this->setStatus(false);
        }
        if (is_null($this->messages)) {
            if ($throwable instanceof Exception) {
                $this->setMessages($throwable->getMessages());
            }
            elseif ($throwable instanceof ValidationException) {
                $this->setMessages($throwable->errors());
            }
            else {
                $this->setMessages($throwable->getMessage());
            }
        }
        if (is_null($this->data)) {
            if ($throwable instanceof Exception) {
                if (count($data = $throwable->getData())) {
                    $this->setData($data);
                }
            }
        }
        if (is_null($this->errorCode)) {
            $this->setErrorCode($throwable->getCode());
        }
        if (is_null($this->statusCode)) {
            if ($throwable instanceof HttpExceptionInterface) {
                $this->setStatusCode($throwable->getStatusCode());
            }
            elseif ($throwable instanceof ValidationException) {
                $this->setStatusCode($throwable->status);
            }
            elseif ($throwable instanceof AuthenticationException) {
                $this->setStatusCode(401);
            }
            else {
                $this->setStatusCode(500);
            }
        }
        if ($throwable instanceof HttpExceptionInterface) {
            $this->setHeaders($throwable->getHeaders());
        }
        return $this;
    }

    public function setErrorCode(int|string $errorCode): static
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    public function setStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode ?? 200;
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

    public function toArray(): array
    {
        return [
            '_status' => $this->status ?? true,
            '_status_code' => $this->getStatusCode(),
            '_error_code' => $this->errorCode,
            '_exception' => config('app.debug') ? $this->throwable : null,
            '_messages' => $this->messages,
            '_data' => $this->data,
        ];
    }
}