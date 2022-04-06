<?php

namespace App\Support\Client;

use Closure;

trait InternalSettingsTrait
{
    protected ?array $currentSettings = null;

    protected string|array|null $internalSettings = null;

    protected bool $internalSettingsPermanently = false;

    protected string|array|null $forcedInternalSettings = null;

    protected ?array $currentInternalSettings = null;

    protected ?array $finalInternalSettings = null;

    protected function captureCurrentSettings(): static
    {
        $this->currentSettings = Client::settings()->toArray();
        return $this;
    }

    protected function getCurrentSettings(): array
    {
        return !is_array($this->currentSettings)
            ? ($this->currentSettings = [])
            : $this->currentSettings;
    }

    protected function getInternalSettings(): array
    {
        return !is_array($this->internalSettings)
            ? ($this->internalSettings = Settings::parseConfig($this->internalSettings))
            : $this->internalSettings;
    }

    protected function getForcedInternalSettings(): array
    {
        return !is_array($this->forcedInternalSettings)
            ? ($this->forcedInternalSettings = Settings::parseConfig($this->forcedInternalSettings))
            : $this->forcedInternalSettings;
    }

    public function setForcedInternalSettings(array|string|null $forcedInternalSettings): static
    {
        $this->forcedInternalSettings = $forcedInternalSettings;
        return $this;
    }

    public function getCurrentInternalSettings(): array
    {
        return !is_array($this->currentInternalSettings)
            ? ($this->currentInternalSettings = array_merge(
                $this->getInternalSettings(),
                ($this->internalSettingsPermanently ? [] : $this->getForcedInternalSettings())
            ))
            : $this->currentInternalSettings;
    }

    public function getFinalInternalSettings(): array
    {
        return !is_array($this->finalInternalSettings)
            ? ($this->finalInternalSettings = array_merge($this->getCurrentSettings(), $this->getCurrentInternalSettings()))
            : $this->finalInternalSettings;
    }

    protected function withInternalSettings(Closure $callback)
    {
        return Client::settingsTemporary($this->getFinalInternalSettings(), $callback);
    }
}