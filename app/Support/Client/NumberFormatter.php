<?php

/**
 * Base
 */

namespace App\Support\Client;

class NumberFormatter extends SettingsApplier
{
    public const DEFAULT_DECIMALS = 2;

    private string $type;

    public function applySettings(Settings $settings): static
    {
        $this->type = $settings->numberFormat;
        return $this;
    }

    public function format(float|int $number, int $decimals = NumberFormatter::DEFAULT_DECIMALS): string
    {
        return match ($this->type) {
            'point_comma' => $this->formatPointComma($number, $decimals),
            'point_space' => $this->formatPointSpace($number, $decimals),
            'comma_point' => $this->formatCommaPoint($number, $decimals),
            'comma_space' => $this->formatCommaSpace($number, $decimals),
            default => $number,
        };
    }

    public function formatInt(float|int $number): string
    {
        return $this->format($number, 0);
    }

    public function fromFormat($formattedNumber): float
    {
        return match ($this->type) {
            'point_comma', 'point_space' => $this->fromFormatPoint($formattedNumber),
            'comma_point', 'comma_space' => $this->fromFormatComma($formattedNumber),
            default => (float)$formattedNumber,
        };
    }

    public function formatPointComma(float|int $number, int $decimals = NumberFormatter::DEFAULT_DECIMALS): string
    {
        return number_format($number, $decimals);
    }

    public function formatPointSpace(float|int $number, int $decimals = NumberFormatter::DEFAULT_DECIMALS): string
    {
        return number_format($number, $decimals, '.', ' ');
    }

    public function formatCommaPoint(float|int $number, int $decimals = NumberFormatter::DEFAULT_DECIMALS): string
    {
        return number_format($number, $decimals, ',', '.');
    }

    public function formatCommaSpace(float|int $number, int $decimals = NumberFormatter::DEFAULT_DECIMALS): string
    {
        return number_format($number, $decimals, ',', ' ');
    }

    public function fromFormatPoint(string $formattedNumber): float
    {
        return (float)preg_replace('/[^\d\.]+/', '', $formattedNumber);
    }

    public function fromFormatComma(string $formattedNumber): float
    {
        return (float)str_replace(',', '.', preg_replace('/[^\d\,]+/', '', $formattedNumber));
    }
}
