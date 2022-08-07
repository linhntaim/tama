<?php

namespace App\Trading\Bots\Data;

use RuntimeException;

class Values
{
    public const SPIKE_NONE = 0;
    public const SPIKE_TROUGH = -1;
    public const SPIKE_PEAK = 1;

    protected array $keys;

    protected int $count;

    protected array $spikes;

    public function __construct(
        protected array $values
    )
    {
        $this->keys = array_keys($this->values);
        $this->count = count($this->keys);

        $this->spikes = [];
    }

    public function has($key)
    {
        return isset($this->values[$key]);
    }

    public function value($key)
    {
        if (!$this->has($key)) {
            throw new RuntimeException('Key does not exist.');
        }
        return $this->values[$key];
    }

    public function spike($key): int
    {
        if (!$this->has($key)) {
            throw new RuntimeException(sprintf('Undefined array key "%s"', $key));
        }

        if (!isset($this->spikes[$key])) {
            $this->determine($key);
        }
        return $this->spikes[$key];
    }

    public function isTrough($key): bool
    {
        return $this->spike($key) == self::SPIKE_TROUGH;
    }

    public function isPeak($key): bool
    {
        return $this->spike($key) == self::SPIKE_PEAK;
    }

    public function isNone($key): bool
    {
        return $this->spike($key) == self::SPIKE_NONE;
    }

    protected function determine($key)
    {
        $value = $this->value($key);
        $i = array_search($key, $this->keys);

        $nextKey = $this->keys[$i + 1];
        if ($this->has($nextKey)) {
            $nextValue = $this->value($nextKey);

            // trough
            if ($nextValue > $value
                && (function ($prev) use ($value) {
                    $prevValue = $value;
                    $hasPrevValue = false;
                    while ($prev >= 0
                        && ($hasPrevValue = $this->has($prevKey = $this->keys[$prev]))
                        && (($prevValue = $this->value($prevKey)) == $value)) {
                        $this->spikes[$prevKey] = self::SPIKE_NONE;
                        --$prev;
                    }
                    return $hasPrevValue && $prevValue > $value;
                })($i - 1)) {
                $this->spikes[$key] = self::SPIKE_TROUGH;
                return;
            }

            // peak
            if ($nextValue < $value
                && (function ($prev) use ($value) {
                    $prevValue = $value;
                    while (($hasPrevValue = $this->has($prevKey = $this->keys[$prev]))
                        && (($prevValue = $this->value($prevKey)) == $value)) {
                        $this->spikes[$prevKey] = self::SPIKE_NONE;
                        --$prev;
                    }
                    return $hasPrevValue && $prevValue < $value;
                })($i - 1)) {
                $this->spikes[$key] = self::SPIKE_PEAK;
                return;
            }
        }

        $this->spikes[$key] = self::SPIKE_NONE;
    }
}
