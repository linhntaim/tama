<?php

namespace App\Trading\Bots\Data;

use RuntimeException;

class Values
{
    public const SPIKE_NONE = 0;
    public const SPIKE_TROUGH = -1;
    public const SPIKE_PEAK = 1;

    protected int $count;

    /**
     * @var int[]|string[]
     */
    protected array $keys;

    /**
     * @var int[]
     */
    protected array $spikes;

    /**
     * @param float[] $values
     */
    public function __construct(
        protected array $values
    )
    {
        $this->keys = array_keys($this->values);
        $this->count = count($this->keys);

        $this->spikes = [];
    }

    public function has(int|string $key): bool
    {
        return isset($this->values[$key]);
    }

    public function value(int|string $key): string
    {
        if (!$this->has($key)) {
            throw new RuntimeException('Key does not exist.');
        }
        return num_std($this->values[$key]);
    }

    public function spike(int|string $key): int
    {
        if (!$this->has($key)) {
            throw new RuntimeException(sprintf('Undefined array key "%s"', $key));
        }

        if (!isset($this->spikes[$key])) {
            $this->determine($key);
        }
        return $this->spikes[$key];
    }

    public function isTrough(int|string $key): bool
    {
        return $this->spike($key) === self::SPIKE_TROUGH;
    }

    public function isPeak(int|string $key): bool
    {
        return $this->spike($key) === self::SPIKE_PEAK;
    }

    public function isNone(int|string $key): bool
    {
        return $this->spike($key) === self::SPIKE_NONE;
    }

    protected function determine(int|string $key): void
    {
        $value = $this->value($key);
        $index = array_search($key, $this->keys, true);

        $nextKey = $this->keys[$index + 1];
        if ($this->has($nextKey)) {
            $nextValue = $this->value($nextKey);

            $comp = num_comp($nextValue, $value);
            if (($comp !== 0) && ($prevValue = $this->getDifferentPrevValue($value, $index)) !== false) {
                // trough
                if ($comp === 1 && num_gt($prevValue, $value)) { // prev > value < next
                    $this->spikes[$key] = self::SPIKE_TROUGH;
                    return;
                }
                // peak
                if ($comp === -1 && num_lt($prevValue, $value)) { // prev < value > next
                    $this->spikes[$key] = self::SPIKE_PEAK;
                    return;
                }
            }
        }

        $this->spikes[$key] = self::SPIKE_NONE;
    }

    protected function getDifferentPrevValue(string $value, int $index): bool|string
    {
        $hasPrevValue = false;
        $prevValue = $value;
        $prevIndex = $index - 1;
        while ($prevIndex >= 0
            && ($hasPrevValue = $this->has($prevKey = $this->keys[$prevIndex]))
            && num_eq($prevValue = $this->value($prevKey), $value)) {
            $this->spikes[$prevKey] = self::SPIKE_NONE;
            --$prevIndex;
        }
        return $hasPrevValue ? $prevValue : false;
    }
}
