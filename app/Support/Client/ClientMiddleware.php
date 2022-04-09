<?php

/**
 * Base
 */

namespace App\Support\Client;

use App\Http\Middleware\EncryptCookies;
use App\Support\Configuration;
use App\Support\Http\Request;
use Closure;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next, ?string $source = null)
    {
        foreach ([
                     'viaHeader',
                     'viaCookie',
                     'viaRoute',
                 ] as $method) {
            if ($this->{$method}($request, $source) !== false) {
                break;
            }
        }

        $this->viaInput($request);
        $this->applySettings();
        return $this->storeCookie($request, $next($request));
    }

    protected function viaHeader(Request $request, ?string $source = null): Manager|bool
    {
        if (is_null($source) || $source === 'header') {
            if (!is_null($settings = $request->headerJson('x-settings'))) {
                return $this->mergeSettings($settings);
            }
        }
        return false;
    }

    protected function viaCookie(Request $request, ?string $source = null): Manager|bool
    {
        if (is_null($source) || $source === 'cookie') {
            if (!is_null($settings = $request->cookieJson(name_starter('settings')))) {
                return $this->mergeSettings($settings);
            }
        }
        return false;
    }

    protected function viaRoute(Request $request, ?string $source = null): Manager|bool
    {
        if (is_null($source) || $source === 'route') {
            foreach (Settings::routes() as $routeMatch => $settings) {
                if ($request->is($routeMatch)) {
                    return $this->mergeSettings($settings);
                }
            }
        }
        return false;
    }

    protected function viaInput(Request $request): Manager|bool
    {
        $settings = Settings::parseConfig($request->input('x_client'));
        foreach (Settings::names() as $name) {
            if (!is_null($value = $request->input("x_$name"))) {
                $settings[$name] = $value;
            }
        }
        if (count($settings)) {
            return $this->mergeSettings($settings, false);
        }
        return false;
    }

    protected function mergeSettings(string|array $settings, bool $permanently = true): Manager
    {
        return Client::settingsMerge($settings, $permanently, false);
    }

    protected function applySettings(): Manager
    {
        return Client::settingsApply();
    }

    protected function storeCookie(Request $request, $response)
    {
        if (Client::settingsChanged()) {
            if (EncryptCookies::ran()) {
                return $response->cookie(name_starter('settings'), Client::settings()->toJson(JSON_READABLE), Configuration::COOKIE_FOREVER_EXPIRE);
            }
        }
        return $response;
    }
}