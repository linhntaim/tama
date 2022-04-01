<?php

/**
 * Base
 */

namespace App\Support\Client;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;

class DateTimer extends SettingsApplier
{
    public const DEFAULT_LOCALE = 'en';
    public const DATABASE_FORMAT_DATE = 'Y-m-d';
    public const DATABASE_FORMAT_TIME = 'H:i:s';
    public const DATABASE_FORMAT = DateTimer::DATABASE_FORMAT_DATE . ' ' . DateTimer::DATABASE_FORMAT_TIME;
    public const DAY_TYPE_NONE = 0;
    public const DAY_TYPE_START_NEXT = 1;
    public const DAY_TYPE_START = -1;
    public const DAY_TYPE_END = -2;
    public const LONG_DATE_FUNCTION = 'longDate';
    public const SHORT_DATE_FUNCTION = 'shortDate';
    public const LONG_TIME_FUNCTION = 'longTime';
    public const SHORT_TIME_FUNCTION = 'shortTime';
    public const ALLOWED_COMPOUND_FUNCTIONS = [
        self::LONG_DATE_FUNCTION,
        self::SHORT_DATE_FUNCTION,
        self::LONG_TIME_FUNCTION,
        self::SHORT_TIME_FUNCTION,
    ];

    #region Static
    protected static Carbon $now;

    public static function now(bool $reset = false): Carbon
    {
        if (is_null(static::$now) || $reset) {
            static::$now = Carbon::now(new CarbonTimeZone('UTC'));
        }
        return clone static::$now;
    }

    public static function databaseNow(bool $reset = false): string
    {
        return static::now($reset)->format(static::DATABASE_FORMAT);
    }

    public static function availableUtcOffsets(): array
    {
        return [
            -12, -11.5, -11, -10.5, -10, -9.5, -9, -8.5, -8, -7.5, -7, -6.5, -6, -5.5, -5, -4.5, -4, -3.5, -3, -2.5, -2, -1.5, -1, -0.5,
            0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 7.5, 8, 8.5, 8.75, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.75, 13, 13.75, 14,
        ];
    }

    public static function availableTimezones(): array
    {
        // UTC
        $timezones = ['UTC'];
        // Timezone by UTC offsets
        foreach (static::availableUtcOffsets() as $offset) {
            $timezones[] = 'UTC' . (0 <= $offset ? '+' . $offset : (string)$offset);
        }
        // UNIX Timezones
        foreach (CarbonTimeZone::listIdentifiers() as $zone) {
            $timezones[] = $zone;
        }
        return $timezones;
    }

    public static function timeOffsetByTimezone(string $timezone): int
    {
        if ($timezone == 'UTC') {
            return 0;
        }
        if (str($timezone)->startsWith('UTC')) {
            return (int)(floatval(str($timezone)->substr(3)->toString()) * 3600);
        }
        return (new CarbonTimeZone($timezone))->getOffset(new Carbon());
    }

    public static function availableDaysOfWeek(): array
    {
        return range(1, 7);
    }

    public static function availableLongDateFormats(): array
    {
        return range(0, 3);
    }

    public static function availableShortDateFormats(): array
    {
        return range(0, 3);
    }

    public static function availableLongTimeFormats(): array
    {
        return range(0, 4);
    }

    public static function availableShortTimeFormats(): array
    {
        return range(0, 4);
    }

    #endregion

    protected string $locale;

    protected ?array $transTerms;

    protected string $transLongDate;

    protected string $transShortDate;

    protected string $transShortMonth;

    protected string $transLongTime;

    protected string $transShortTime;

    protected int $timeOffset;

    public function applySettings(Settings $settings): static
    {
        $this->locale = $settings->locale;
        $this->transTerms = $this->buildTransTerms();
        $this->transLongDate = 'date_timer.formats.long_date_' . $settings->longDateFormat;
        $this->transShortDate = 'date_timer.formats.short_date_' . $settings->shortDateFormat;
        $this->transShortMonth = 'date_timer.formats.short_month_' . $settings->shortDateFormat;
        $this->transLongTime = 'date_timer.formats.long_time_' . $settings->longTimeFormat;
        $this->transShortTime = 'date_timer.formats.short_time_' . $settings->shortTimeFormat;
        $this->timeOffset = $this->timeOffsetByTimezone($settings->timezone);
        return $this;
    }

    public function getTimeOffset(): int
    {
        return $this->timeOffset;
    }

    protected function buildTransTerms(): ?array
    {
        if ($this->locale == self::DEFAULT_LOCALE) {
            return null;
        }

        $defaultTerms = [];
        $localeTerms = [];
        for ($i = 1; $i <= 7; ++$i) {
            $defaultTerms[] = trans('date_timer.day_' . $i, [], self::DEFAULT_LOCALE);
            $localeTerms[] = trans('date_timer.day_' . $i, [], $this->locale);
        }
        for ($i = 1; $i <= 7; ++$i) {
            $defaultTerms[] = trans('date_timer.short_day_' . $i, [], self::DEFAULT_LOCALE);
            $localeTerms[] = trans('date_timer.short_day_' . $i, [], $this->locale);
        }
        for ($i = 1; $i <= 12; ++$i) {
            $defaultTerms[] = trans('date_timer.month_' . $i, [], self::DEFAULT_LOCALE);
            $localeTerms[] = trans('date_timer.month_' . $i, [], $this->locale);
        }
        for ($i = 1; $i <= 12; ++$i) {
            $defaultTerms[] = trans('date_timer.short_month_' . $i, [], self::DEFAULT_LOCALE);
            $localeTerms[] = trans('date_timer.short_month_' . $i, [], $this->locale);
        }
        foreach (['lm', 'um'] as $c) {
            foreach (['am', 'pm'] as $m) {
                $defaultTerms[] = trans('date_timer.' . $c . '_' . $m, [], self::DEFAULT_LOCALE);
                $localeTerms[] = trans('date_timer.' . $c . '_' . $m, [], $this->locale);
            }
        }
        return [
            'default' => $defaultTerms,
            'locale' => $localeTerms,
        ];
    }

    #region Options
    public function timezoneOptions(): array
    {
        // UTC
        $timezones = [
            [
                'name' => 'UTC',
                'timezones' => [
                    [
                        'name' => 'UTC',
                        'value' => 'UTC',
                    ],
                ],
            ],
        ];
        // Timezone by UTC offsets
        $utcOffsets = [];
        foreach (self::availableUtcOffsets() as $offset) {
            $offsetValue = 'UTC' . (0 <= $offset ? '+' . $offset : (string)$offset);
            $offsetName = str_replace(['.25', '.5', '.75'], [':15', ':30', ':45'], $offsetValue);
            $utcOffsets[] = [
                'name' => $offsetName,
                'value' => $offsetValue,
            ];
        }
        $timezones[] = [
            'name' => trans('date_timer.utc_offsets', [], $this->locale),
            'timezones' => $utcOffsets,
        ];
        // UNIX Timezones
        $unixTimezones = [];
        $currentContinent = null;
        foreach (CarbonTimeZone::listIdentifiers() as $zone) {
            $zonePart = explode('/', $zone);
            $continent = $zonePart[0];

            if ($continent == 'UTC') {
                continue;
            }

            if (!empty($currentContinent) && $continent != $currentContinent) {
                $timezones[] = [
                    'name' => $currentContinent,
                    'timezones' => $unixTimezones,
                ];
                $unixTimezones = [];
            }
            $currentContinent = $continent;
            $city = $zonePart[1] ?? '';
            $subCity = $zonePart[2] ?? '';
            $unixTimezones[] = [
                'name' => str_replace('_', ' ', $city) . (empty($subCity) ? '' : ' - ' . str_replace('_', ' ', $subCity)),
                'value' => $zone,
            ];
        }
        $timezones[] = [
            'group_name' => $currentContinent,
            'group_values' => $unixTimezones,
        ];
        return $timezones;
    }

    public function dayOfWeekOptions(): array
    {
        $options = [];
        for ($i = 1; $i <= 7; ++$i) {
            $options[] = [
                'name' => trans('date_timer.day_' . $i, [], $this->locale),
                'value' => $i,
            ];
        }
        return $options;
    }

    public function longDateFormatOptions(): array
    {
        $options = [];
        for ($i = 0; $i <= 3; ++$i) {
            $options[] = [
                'value' => $i,
                'text' => trans('date_timer.long_date_' . $i, $this->exampleBags(), $this->locale),
            ];
        }
        return $options;
    }

    public function shortDateFormatOptions(): array
    {
        $options = [];
        for ($i = 0; $i <= 3; ++$i) {
            $options[] = [
                'name' => trans('date_timer.short_date_' . $i, $this->exampleBags(), $this->locale),
                'value' => $i,
            ];
        }
        return $options;
    }

    public function longTimeFormatOptions(): array
    {
        $options = [];
        for ($i = 0; $i <= 4; ++$i) {
            $options[] = [
                'name' => trans('date_timer.long_time_' . $i, $this->exampleBags(), $this->locale),
                'value' => $i,
            ];
        }
        return $options;
    }

    public function shortTimeFormatOptions(): array
    {
        $options = [];
        for ($i = 0; $i <= 4; ++$i) {
            $options[] = [
                'name' => trans('date_timer.short_time_' . $i, $this->exampleBags(), $this->locale),
                'value' => $i,
            ];
        }
        return $options;
    }

    #endregion

    protected function applyDayType(Carbon $time, int $dayType = DateTimer::DAY_TYPE_NONE): Carbon
    {
        return match ($dayType) {
            self::DAY_TYPE_END => $time->setTime(23, 59, 59),
            self::DAY_TYPE_START => $time->setTime(0, 0),
            self::DAY_TYPE_START_NEXT => $time->setTime(0, 0)->addDay(),
            default => $time,
        };
    }

    #region UTC -> Client
    public function carbon(Carbon|string $time = 'now'): Carbon
    {
        return ($time instanceof Carbon
            ? (clone $time)->setTimezone(new CarbonTimeZone('UTC'))
            : new Carbon($time, new CarbonTimeZone('UTC')))
            ->addSeconds($this->timeOffset);
    }

    public function time(Carbon|string $time = 'now', int $dayType = DateTimer::DAY_TYPE_NONE): Carbon
    {
        return $this->applyDayType(
            $this->carbon($time),
            $dayType
        );
    }

    protected function bags(Carbon|string $time = 'now', int $dayType = DateTimer::DAY_TYPE_NONE): array
    {
        $time = $this->time($time, $dayType);
        return [
            'd' => $time->format('j'),
            'dd' => $time->format('d'),
            'sd' => trans('date_timer.short_day_' . $time->format('N'), [], $this->locale),
            'ld' => trans('date_timer.day_' . $time->format('N'), [], $this->locale),
            'm' => $time->format('n'),
            'mm' => $time->format('m'),
            'sm' => trans('date_timer.short_month_' . $time->format('n'), [], $this->locale),
            'lm' => trans('date_timer.month_' . $time->format('n'), [], $this->locale),
            'yy' => $time->format('y'),
            'yyyy' => $time->format('Y'),
            'h' => $time->format('g'),
            'hh' => $time->format('h'),
            'h2' => $time->format('G'),
            'hh2' => $time->format('H'),
            'ii' => $time->format('i'),
            'ss' => $time->format('s'),
            'ut' => trans('date_timer.um_' . $time->format('a'), [], $this->locale),
            'lt' => trans('date_timer.lm_' . $time->format('a'), [], $this->locale),
        ];
    }

    protected function exampleBags(): array
    {
        return $this->bags(self::now()->year . '-12-24 08:00:00', true);
    }

    protected function longDateWithBags(array $bags): string
    {
        return trans($this->transLongDate, $bags, $this->locale);
    }

    protected function shortDateWithBags(array $bags): string
    {
        return trans($this->transShortDate, $bags, $this->locale);
    }

    protected function shortMonthWithBags(array $bags): string
    {
        return trans($this->transShortMonth, $bags, $this->locale);
    }

    protected function longTimeWithBags(array $bags): string
    {
        return trans($this->transLongTime, $bags, $this->locale);
    }

    protected function shortTimeWithBags(array $bags): string
    {
        return trans($this->transShortTime, $bags, $this->locale);
    }

    public function longDate(Carbon|string $time = 'now', int $dayType = DateTimer::DAY_TYPE_NONE): string
    {
        return $this->longDateWithBags($this->bags($time, $dayType));
    }

    public function shortDate(Carbon|string $time = 'now', int $dayType = DateTimer::DAY_TYPE_NONE): string
    {
        return $this->shortDateWithBags($this->bags($time, $dayType));
    }

    public function shortMonth(Carbon|string $time = 'now', int $dayType = DateTimer::DAY_TYPE_NONE): string
    {
        return $this->shortMonthWithBags($this->bags($time, $dayType));
    }

    public function longTime(Carbon|string $time = 'now', int $dayType = DateTimer::DAY_TYPE_NONE): string
    {
        return $this->longTimeWithBags($this->bags($time, $dayType));
    }

    public function shortTime(Carbon|string $time = 'now', int $dayType = DateTimer::DAY_TYPE_NONE): string
    {
        return $this->shortTimeWithBags($this->bags($time, $dayType));
    }

    public function compound(
        string        $func1 = DateTimer::SHORT_DATE_FUNCTION,
        string        $separation = ' ',
        string        $func2 = DateTimer::SHORT_TIME_FUNCTION,
        Carbon|string $time = 'now',
        int           $dayType = DateTimer::DAY_TYPE_NONE
    ): ?string
    {
        if (!in_array($func1, self::ALLOWED_COMPOUND_FUNCTIONS) || !in_array($func2, self::ALLOWED_COMPOUND_FUNCTIONS)) {
            return null;
        }
        $bags = $this->bags($time, $dayType);
        return sprintf('%s%s%s', $this->{$func1 . 'WithBags'}($bags), $separation, $this->{$func2 . 'WithBags'}($bags));
    }

    public function format(string $name, Carbon|string $time = 'now', int $dayType = DateTimer::DAY_TYPE_NONE): string
    {
        return trans('date_timer.formats.' . $name, $this->bags($time, $dayType), $this->locale);
    }

    protected function toLocaleTimeString(string $time): string
    {
        return is_null($this->transTerms)
            ? $time
            : str_replace($this->transTerms['default'], $this->transTerms['locale'], $time);
    }

    public function custom($format, Carbon|string $time = 'now', int $dateType = DateTimer::DAY_TYPE_NONE): string
    {
        return $this->toLocaleTimeString($this->time($time, $dateType)->format($format));
    }

    protected function formatBags(): array
    {
        return [
            'd' => 'j',
            'dd' => 'd',
            'sd' => 'D',
            'ld' => 'l',
            'm' => 'n',
            'mm' => 'm',
            'sm' => 'M',
            'lm' => 'F',
            'yy' => 'y',
            'yyyy' => 'Y',
            'h' => 'g',
            'hh' => 'h',
            'h2' => 'G',
            'hh2' => 'H',
            'ii' => 'i',
            'ss' => 's',
            'ut' => 'A',
            'lt' => 'a',
        ];
    }

    public function longDateFormat(): string
    {
        return $this->longDateWithBags($this->formatBags());
    }

    public function shortDateFormat(): string
    {
        return $this->shortDateWithBags($this->formatBags());
    }

    public function shortMonthFormat(): string
    {
        return $this->shortMonthWithBags($this->formatBags());
    }

    public function longTimeFormat(): string
    {
        return $this->longTimeWithBags($this->formatBags());
    }

    public function shortTimeFormat(): string
    {
        return $this->shortTimeWithBags($this->formatBags());
    }

    public function compoundFormat(string $func1, string $separation, string $func2): ?string
    {
        if (!in_array($func1, self::ALLOWED_COMPOUND_FUNCTIONS) || !in_array($func2, self::ALLOWED_COMPOUND_FUNCTIONS)) {
            return null;
        }
        return sprintf('%s%s%s', $this->{$func1 . 'Format'}(), $separation, $this->{$func2 . 'Format'}());
    }
    #endregion

    #region Client -> UTC
    protected function fromLocaleTimeString(string $time): string
    {
        return is_null($this->transTerms)
            ? $time
            : str_replace($this->transTerms['locale'], $this->transTerms['default'], $time);
    }

    public function fromCarbon(Carbon|string $time): Carbon
    {
        return $time instanceof Carbon
            ? (clone $time)->setTimezone(new CarbonTimeZone('UTC'))
            : (new Carbon($this->fromLocaleTimeString($time), new CarbonTimeZone('UTC')))->subSeconds($this->timeOffset);
    }

    public function from(Carbon|string $time, int $dayType = DateTimer::DAY_TYPE_NONE): Carbon
    {
        return $this->applyDayType($this->fromCarbon($time), $dayType);
    }

    public function fromFormat(string $format, string $time, int $dayType = DateTimer::DAY_TYPE_NONE): Carbon
    {
        return $this->from(Carbon::createFromFormat($format, $this->fromLocaleTimeString($time), new CarbonTimeZone('UTC')), $dayType);
    }

    public function fromFormatToFormat(string $format, string $time, $toFormat = null, int $dayType = DateTimer::DAY_TYPE_NONE): string
    {
        return $this->fromFormat($format, $time, $dayType)->format($toFormat ?: $format);
    }

    public function fromFormatToDatabaseFormat(string $format, string $time, int $dayType = DateTimer::DAY_TYPE_NONE): string
    {
        return $this->fromFormatToFormat($format, $time, self::DATABASE_FORMAT, $dayType);
    }
    #endregion
}
