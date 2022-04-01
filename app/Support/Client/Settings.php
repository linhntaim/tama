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
 * @property string $appName
 * @property string $appUrl
 * @property string $locale
 * @property string $country
 * @property string $timezone
 * @property string $currency
 * @property string $numberFormat
 * @property int $firstDayOfWeek
 * @property int $longDateFormat
 * @property int $shortDateFormat
 * @property int $longTimeFormat
 * @property int $shortTimeFormat
 */
class Settings implements ISettings, Arrayable, Jsonable
{
    protected array $settings;

    public function __construct(?array $settings = null)
    {
        $this->setDefault()->merge($settings);
    }

    protected function setDefault(): static
    {
        $client = config_starter('client');
        $defaultSettings = $client['settings'][$client['default']];
        $this->settings = $defaultSettings;
        return $this;
    }

    public function merge(Settings|array|null $settings): static
    {
        if (is_null($settings)) {
            return $this;
        }
        if (is_array($settings)) {
            foreach ($settings as $name => $value) {
                $this->{$name} = $value;
            }
            return $this;
        }
        if ($settings instanceof Settings) {
            return $this->merge($settings->toArray());
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

    private function set(string $name, $value): static
    {
        $this->settings[$name] = $value;
        return $this;
    }

    public function getAppName(): string
    {
        return $this->settings['app_name'] ?? config('app.name');
    }

    public function getAppUrl(): string
    {
        return $this->settings['app_url'] ?? config('app.url');
    }

    public function setLocale(string $locale): static
    {
        if (in_array($locale, config_starter('supported_locales'))) {
            return $this->set('locale', $locale);
        }
        return $this;
    }

    public function getLocale(): string
    {
        return $this->settings['locale'];
    }

    public function setCountry(string $country): static
    {
        if (in_array($country = strtoupper($country), array_keys(config_starter('countries')))) {
            return $this->set('country', $country);
        }
        return $this;
    }

    public function setTimezone(string $timezone): static
    {
        if (in_array($timezone, DateTimer::availableTimezones())) {
            return $this->set('timezone', $timezone);
        }
        return $this;
    }

    public function setCurrency(string $currency): static
    {
        if (in_array($currency = strtoupper($currency), array_keys(config_starter('currencies')))) {
            return $this->set('currency', $currency);
        }
        return $this;
    }

    public function setNumberFormat(string $numberFormat): static
    {
        if (in_array($numberFormat, config_starter('number_formats'))) {
            return $this->set('number_format', $numberFormat);
        }
        return $this;
    }

    public function setFirstDayOfWeek(int $firstDayOfWeek): static
    {
        if (in_array($firstDayOfWeek, DateTimer::availableDaysOfWeek())) {
            return $this->set('first_day_of_week', $firstDayOfWeek);
        }
        return $this;
    }

    public function setLongDateFormat(int $longDateFormat): static
    {
        if (in_array($longDateFormat, DateTimer::availableLongDateFormats())) {
            return $this->set('long_date_format', $longDateFormat);
        }
        return $this;
    }

    public function setShortDateFormat(int $shortDateFormat): static
    {
        if (in_array($shortDateFormat, DateTimer::availableShortDateFormats())) {
            return $this->set('short_date_format', $shortDateFormat);
        }
        return $this;
    }

    public function setLongTimeFormat(int $longTimeFormat): static
    {
        if (in_array($longTimeFormat, DateTimer::availableLongTimeFormats())) {
            return $this->set('long_time_format', $longTimeFormat);
        }
        return $this;
    }

    public function setShortTimeFormat(int $shortTimeFormat): static
    {
        if (in_array($shortTimeFormat, DateTimer::availableShortTimeFormats())) {
            return $this->set('short_time_format', $shortTimeFormat);
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
