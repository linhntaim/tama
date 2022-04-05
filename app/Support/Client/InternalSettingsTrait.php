<?php

namespace App\Support\Client;

use Closure;

trait InternalSettingsTrait
{
    use ParseSettingsTrait;

    protected ?Settings $currentSettings;

    protected string|array|null $internalSettings = null;

    protected bool $internalSettingsPermanently = false;

    protected string|array|null $forcedSettings = null;

    protected function captureCurrentSettings(): static
    {
        $this->currentSettings = clone Client::settings();
        return $this;
    }

    protected function getCurrentSettings(): Settings
    {
        return is_null($this->currentSettings) ? new Settings() : $this->currentSettings;
    }

    protected function getInternalSettings(): array
    {
        return !is_array($this->internalSettings)
            ? ($this->internalSettings = $this->parseSettings($this->internalSettings))
            : $this->internalSettings;
    }

    public function setInternalSettings(array|string|null $internalSettings): static
    {
        $this->internalSettings = $internalSettings;
        return $this;
    }

    protected function getForcedSettings(): array
    {
        return !is_array($this->forcedSettings)
            ? ($this->forcedSettings = $this->parseSettings($this->forcedSettings))
            : $this->forcedSettings;
    }

    public function setForcedSettings(array|string|null $forcedSettings): static
    {
        $this->forcedSettings = $forcedSettings;
        return $this;
    }

    protected function finalInternalSettings(): array
    {
        return $this->getInternalSettings() + ($this->internalSettingsPermanently ? [] : $this->getForcedSettings());
    }

    protected function withInternalSettings(Closure $callback)
    {
        return count($settings = $this->finalInternalSettings())
            ? Client::settingsTemporary(
                $this->getCurrentSettings()->merge($settings),
                $callback
            )
            : $callback();
    }
}