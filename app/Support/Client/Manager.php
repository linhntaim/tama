<?php

namespace App\Support\Client;

use Closure;
use Illuminate\Contracts\Foundation\Application;

class Manager
{
    protected Application $app;

    protected Settings $settings;

    /**
     * @var array|SettingsApplier[]
     */
    protected array $settingsAppliers;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->settings = new Settings();
        $this
            ->addSettingApplier('date_timer', new DateTimer($this->settings))
            ->addSettingApplier('number_formatter', new NumberFormatter($this->settings))
            ->settingsApply();
    }

    public function settings(): Settings
    {
        return clone $this->settings;
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

    public function settingsMerge(Settings|string|array|null $settings, bool $permanently = false, bool $apply = true): static
    {
        $this->settings->merge($settings, $permanently);
        return $apply ? $this->settingsApply() : $this;
    }

    public function settingsApply(): static
    {
        $this->app->setLocale($this->settings->locale);
        foreach ($this->settingsAppliers as $settingsApplier) {
            $settingsApplier->applySettings($this->settings);
        }
        return $this;
    }

    public function settingsChanged(): bool
    {
        return $this->settings->hasChanges();
    }

    public function settingsTemporary(Settings|string|array|null $settings, Closure $callback, mixed ...$args): mixed
    {
        if ($this->settings->isDiffFrom($settings)) {
            $origin = $this->settings();
            $this->settingsMerge($settings);
            $called = value($callback, ...$args);
            $this->settingsMerge($origin);
            return $called;
        }
        return $callback();
    }
}
