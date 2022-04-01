<?php

/**
 * Base
 */

namespace App\Support\Client;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Class Settings
 * @package App\Support\Client
 * @property string $locale
 * @property string $country
 * @property string $timezone
 * @property string $currency
 * @property string $numberFormat
 * @property int $longDateFormat
 * @property int $shortDateFormat
 * @property int $longTimeFormat
 * @property int $shortTimeFormat
 */
class Settings implements ISettings, Arrayable, Jsonable
{
    protected array $settings;

    protected array $changes;

    public function __construct(?array $settings = null)
    {
        $this
            ->clearChanges()
            ->setDefault()
            ->merge($settings, true);
    }

    public function hasChanges(): bool
    {
        return count($this->changes);
    }

    public function clearChanges(): static
    {
        $this->changes = [];
        return $this;
    }

    protected function setDefault(): static
    {
        $client = config_starter('client');
        $defaultSettings = $client['settings'][$client['default']];
        $this->settings = $defaultSettings;
        return $this;
    }

    public function merge(Settings|array|null $settings, bool $permanently = false): static
    {
        if (is_null($settings)) {
            return $this;
        }
        if (is_array($settings)) {
            foreach ($settings as $name => $value) {
                if (method_exists($this, $method = 'set' . str($name)->studly()->toString())) {
                    $this->{$method}($value);
                }
                else {
                    $this->set($name, $value, $permanently);
                }
            }
            return $this;
        }
        if ($settings instanceof Settings) {
            return $this->merge($settings->toArray(), $permanently);
        }
        return $this;
    }

    public function __get(string $name)
    {
        $name = str($name);
        if (method_exists($this, $method = 'get' . $name->studly()->toString())) {
            return $this->{$method}();
        }
        return $this->settings[$name->snake()->toString()] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $name = str($name);
        if (method_exists($this, $method = 'set' . $name->studly()->toString())) {
            $this->{$method}($value);
        }
        else {
            $this->set($name->snake()->toString(), $value);
        }
    }

    public function set(string $name, $value, bool $permanently = false): static
    {
        if (!$permanently && ($this->settings[$name] ?? null) != $value) {
            $this->changes[] = $name;
        }
        $this->settings[$name] = $value;
        return $this;
    }

    public function setLocale(string $locale, bool $permanently = false): static
    {
        if (in_array($locale, config_starter('supported_locales'))) {
            return $this->set('locale', $locale, $permanently);
        }
        return $this;
    }

    public function getLocale(): string
    {
        return $this->settings['locale'];
    }

    public function setCountry(string $country, bool $permanently = false): static
    {
        if (in_array($country = strtoupper($country), array_keys(config_starter('countries')))) {
            return $this->set('country', $country, $permanently);
        }
        return $this;
    }

    public function setTimezone(string $timezone, bool $permanently = false): static
    {
        if (in_array($timezone, DateTimer::availableTimezones())) {
            return $this->set('timezone', $timezone, $permanently);
        }
        return $this;
    }

    public function setCurrency(string $currency, bool $permanently = false): static
    {
        if (in_array($currency = strtoupper($currency), array_keys(config_starter('currencies')))) {
            return $this->set('currency', $currency, $permanently);
        }
        return $this;
    }

    public function setNumberFormat(string $numberFormat, bool $permanently = false): static
    {
        if (in_array($numberFormat, config_starter('number_formats'))) {
            return $this->set('number_format', $numberFormat, $permanently);
        }
        return $this;
    }

    public function setLongDateFormat(int $longDateFormat, bool $permanently = false): static
    {
        if (in_array($longDateFormat, DateTimer::availableLongDateFormats())) {
            return $this->set('long_date_format', $longDateFormat, $permanently);
        }
        return $this;
    }

    public function setShortDateFormat(int $shortDateFormat, bool $permanently = false): static
    {
        if (in_array($shortDateFormat, DateTimer::availableShortDateFormats())) {
            return $this->set('short_date_format', $shortDateFormat, $permanently);
        }
        return $this;
    }

    public function setLongTimeFormat(int $longTimeFormat, bool $permanently = false): static
    {
        if (in_array($longTimeFormat, DateTimer::availableLongTimeFormats())) {
            return $this->set('long_time_format', $longTimeFormat, $permanently);
        }
        return $this;
    }

    public function setShortTimeFormat(int $shortTimeFormat, bool $permanently = false): static
    {
        if (in_array($shortTimeFormat, DateTimer::availableShortTimeFormats())) {
            return $this->set('short_time_format', $shortTimeFormat, $permanently);
        }
        return $this;
    }

    public function toArray(): array
    {
        return $this->settings;
    }

    public function toJson($options = 0): bool|string
    {
        return json_encode($this->toArray(), $options);
    }
}
