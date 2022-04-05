<?php

namespace App\Support\Client;

trait ParseSettingsTrait
{
    protected function parseSettings(string|array|null $settings)
    {
        if (is_array($settings)) {
            return $settings;
        }
        if (is_string($settings)) {
            return config_starter('client.settings')[$settings] ?? [];
        }
        return [];
    }

    protected function settingsNames(): array
    {
        return array_keys(config_starter('client.settings.default'));
    }
}