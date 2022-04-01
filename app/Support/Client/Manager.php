<?php

/**
 * Base
 */

namespace App\Support\Client;

use Closure;
use Illuminate\Contracts\Foundation\Application;

class Manager
{
    protected Application $app;

    protected Settings $settings;

    /**
     * @var SettingsApplier[]|array
     */
    protected array $settingsAppliers;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->settings = new Settings();
        $this
            ->addSettingApplier('date_timer', new DateTimer($this->settings))
            ->addSettingApplier('number_formatter', new NumberFormatter($this->settings));
    }

    public function settings(): Settings
    {
        return $this->settings;
    }

    public function dateTimer(): DateTimer
    {
        return $this->settingsAppliers['date_timer'];
    }

    public function numberFormatter(): NumberFormatter
    {
        return $this->settingsAppliers['number_formatter'];
    }

    public function addSettingApplier(string $name, SettingsApplier $settingApplier): static
    {
        $this->settingsAppliers[$name] = $settingApplier;
        return $this;
    }

    public function merge(Settings|array|null $settings): static
    {
        $this->settings->merge($settings);
        return $this->apply();
    }

    public function apply(): static
    {
        $this->app->setLocale($this->settings->locale);
        foreach ($this->settingsAppliers as $settingsApplier) {
            $settingsApplier->applySettings($this->settings);
        }
        return $this;
    }

    public function temporary(Settings|array|null $settings, Closure $callback): static
    {
        $origin = clone $this->settings;
        $this->merge($settings);
        $called = $callback();
        $this->merge($origin);
        return $called;
    }
}