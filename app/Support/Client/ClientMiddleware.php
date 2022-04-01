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
            if ($this->{$method}($request, $source) !== false) {
                break;
            }
        }

        $this->viaQuery($request);
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
            foreach (config_starter('client.routes') as $routeMatch => $settingsName) {
                if ($request->is($routeMatch)) {
                    return $this->mergeSettings(config_starter("client.routes.$settingsName"));
                }
            }
        }
        return false;
    }

    protected function viaQuery(Request $request): Manager|bool
    {
        $settings = !is_null($value = $request->query('x_client'))
            ? config_starter('client.settings')[$value] ?? []
            : [];
        foreach (array_keys(config_starter('client.settings.default')) as $name) {
            if (!is_null($value = $request->query("x_$name"))) {
                $settings[$name] = $value;
            }
        }
        if (count($settings) > 0) {
            return $this->mergeSettings($settings, false);
        }
        return false;
    }

    protected function mergeSettings(array $settings, bool $permanently = true): Manager
    {
        return Client::settingsMerge($settings, $permanently, false);
    }

    protected function applySettings(): Manager
    {
        return Client::settingsApply();
    }

    protected function storeCookie(Request $request, Response $response): Response
    {
        if (Client::settingsChanged()) {
            if ($request->hasSession()) {
                return $response->cookie(name_starter('settings'), Client::settings()->toJson(), Configuration::COOKIE_FOREVER_EXPIRE);
            }
        }
        return $response;
    }
}