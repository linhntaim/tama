<?php

namespace App\Support\Http\Resources;

use App\Support\Exceptions\Exception;
use App\Support\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use JsonSerializable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ResponseResource extends Resource
{
    protected ?string $wrapped = '_data';

    public static function from(mixed $resource = null, mixed ...$args): static
    {
        return $resource instanceof ResponseResource
            ? $resource
            : tap(new static($resource), function (ResponseResource $responseResource) use ($resource, $args) {
                if (is_bool($resource)) {
                    $responseResource
                        ->setResource(null)
                        ->setStatus($resource);
                }
                elseif (is_string($resource)) {
                    $responseResource
                        ->setResource(null)
                        ->setStatus(false)
                        ->setMessages($resource);
                }
                elseif (is_array($resource)) {
                    $responseResource
                        ->setStatus(true);
                }
                elseif ($resource instanceof Throwable) {
                    $responseResource
                        ->setResource(null)
                        ->setException($resource);
                }
                else {
                    $responseResource->setResource(
                        $responseResource->resourceFrom($resource, $args[0] ?? Resource::class)
                    );
                }
            });
    }

    protected array $headers = [];

    protected bool|null $status = null;

    protected int|null $statusCode = null;

    protected int|string|null $errorCode = null;

    protected ?Throwable $throwable = null;

    protected ?array $messages = null;

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
            switch (true) {
                case $throwable instanceof ValidationException:
                    $this->setStatusCode($throwable->status);
                    break;
                case $throwable instanceof AuthenticationException:
                    $this->setStatusCode(401);
                    break;
                case $throwable instanceof HttpExceptionInterface:
                    $this->setStatusCode($throwable->getStatusCode());
                    break;
                default:
                    $this->setStatusCode(500);
                    break;
            }
        }
        if (is_null($this->errorCode)) {
            $this->setErrorCode($throwable->getCode());
        }
        if (is_null($this->messages)) {
            switch (true) {
                case $throwable instanceof NotFoundHttpException:
                    $this->setMessages(($message = $throwable->getMessage()) ? $message : 'Not found.');
                    break;
                case $throwable instanceof ValidationException:
                    $this->setMessages($throwable->validator->errors()->all());
                    break;
                case $throwable instanceof Exception:
                    $this->setMessages($throwable->getMessages());
                    break;
                default:
                    $this->setMessages($throwable->getMessage());
                    break;
            }
        }
        if (is_null($this->resource)) {
            switch (true) {
                case $throwable instanceof ValidationException:
                    $this->setResource([
                        'validation' => $throwable->errors(),
                    ]);
                    break;
                case $throwable instanceof Exception:
                    if (count($data = $throwable->getData())) {
                        $this->setResource($data);
                    }
                    break;
            }
        }
        return $this;
    }

    public function getException(): ?Throwable
    {
        return config('app.debug') ? $this->throwable : null;
    }

    public function getExceptionAsArray(): ?array
    {
        if (is_null($exception = $this->getException())) {
            return null;
        }
        $exceptions = [];
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

    public function setMessages(array|string|null $messages): static
    {
        if (!is_null($messages)) {
            $this->messages = (array)$messages;
        }
        return $this;
    }

    public function getMessages(): ?array
    {
        return $this->messages;
    }

    public function with($request): array
    {
        return array_merge(parent::with($request), [
            '_status' => $this->getStatus(),
            '_status_code' => $this->getStatusCode(),
            '_error_code' => $this->getErrorCode(),
            '_messages' => $this->getMessages(),
            '_exception' => $this->getExceptionAsArray(),
        ]);
    }

    public function jsonOptions(): int
    {
        return JSON_READABLE;
    }

    /**
     * @param Request $request
     * @param JsonResponse $response
     */
    public function withResponse($request, $response)
    {
        $response
            ->setStatusCode($this->getStatusCode())
            ->withHeaders($this->getHeaders());
    }

    public function toArray($request): array|Arrayable|JsonSerializable
    {
        if ($this->resource instanceof IArrayResponsibleResource) {
            return $this->resource->toArrayResponse($request);
        }
        return parent::toArray($request);
    }
}
