<?php

namespace App\Support\Services;

use App\Support\Facades\RateLimiter;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * @method $this get(string $url, $query = null)
 *
 * @mixin PendingRequest
 * @mixin Response
 */
class Service
{
    protected string $baseUrl;

    protected PendingRequest $lastRequest;

    protected Response $lastResponse;

    protected string $accept = 'application/json';

    protected int $maxAttempts = 0;

    protected int $decaySeconds = 60;

    protected string $rateLimiterKey;

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function createRequest(): PendingRequest
    {
        return Http::baseUrl($this->getBaseUrl())->accept($this->accept);
    }

    protected function body(): array|string
    {
        return match ($this->accept) {
            'application/json' => $this->lastResponse?->json(),
            default => $this->lastResponse?->body(),
        };
    }

    protected function response(): bool|array|string
    {
        return $this->successful() ? $this->body() : false;
    }

    protected function getRateLimiterKey(): string
    {
        return $this->rateLimiterKey ?? static::class;
    }

    protected function requestWithLimit($method, $arguments)
    {
        RateLimiter::attemptWithDelay(
            $this->getRateLimiterKey(),
            $this->maxAttempts,
            function () use ($method, $arguments) {
                $this->lastResponse = $this->lastRequest->{$method}(...$arguments);
                return true;
            },
            $this->decaySeconds
        );
    }

    public function __call(string $name, array $arguments)
    {
        if (isset($this->lastResponse) && method_exists($this->lastResponse, $name)) {
            return $this->lastResponse->{$name}(...$arguments);
        }
        $this->lastRequest = $this->createRequest();
        if (method_exists($this->lastRequest, $name)) {
            if (in_array($name, ['get', 'head', 'post', 'patch', 'put', 'delete', 'send'])) {
                $this->requestWithLimit($name, $arguments);
            }
            else {
                return $this->lastRequest->{$name}(...$arguments);
            }
        }
        return $this;
    }
}
