<?php

/**
 * Base
 */

namespace App\Support\Client;

abstract class SettingsApplier
{
    public function __construct(Settings $settings)
    {
        $this->applySettings($settings);
    }

    public abstract function applySettings(Settings $settings): static;
}