<?php

/**
 * Base
 */

namespace App\Support\Client;

use App\Support\Configuration;
use App\Support\Http\Request;
use Closure;
use Illuminate\Http\Response;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next, ?string $source = null)
    {
        foreach ([
                     'viaHeader',
                     'viaCookie',
                     'viaRoute',
                 ] as $method) {
            if ($this->{$method}($request, $source)) {
                break;
            }
        }
        return $this->storeCookie($request, $next($request));
    }

    protected function viaHeader(Request $request, ?string $source = null): bool
    {
        if (is_null($source) || $source === 'header') {
            if (!is_null($settings = $request->headerJson('x-settings'))) {
                return $this->applySettings($request, $settings);
            }
        }
        return false;
    }

    protected function viaCookie(Request $request, ?string $source = null): bool
    {
        if (is_null($source) || $source === 'cookie') {
            if (!is_null($settings = $request->cookieJson(name_starter('settings')))) {
                return $this->applySettings($request, $settings);
            }
        }
        return false;
    }

    protected function viaRoute(Request $request, ?string $source = null): bool
    {
        if (is_null($source) || $source === 'route') {
            foreach (config_starter('client.routes') as $routeMatch => $settingsName) {
                if ($request->is($routeMatch)) {
                    return $this->applySettings($request, config_starter("client.routes.$settingsName"));
                }
            }
        }
        return false;
    }

    protected function applySettings(Request $request, array $settings): bool
    {
        Client::merge($settings);
        return true;
    }

    protected function storeCookie(Request $request, Response $response): Response
    {
        return $request->hasSession()
            ? $response->cookie(name_starter('settings'), Client::settings()->toJson(), Configuration::COOKIE_FOREVER_EXPIRE)
            : $response;
    }
}