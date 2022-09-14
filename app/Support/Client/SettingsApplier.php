<?php

namespace App\Support\Client;

abstract class SettingsApplier
{
    public function __construct(Settings $settings)
    {
        $this->applySettings($settings);
    }

    abstract public function applySettings(Settings $settings): static;
}
