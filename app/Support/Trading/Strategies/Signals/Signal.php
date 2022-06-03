<?php

namespace App\Support\Trading\Strategies\Signals;

use App\Support\Trading\Strategies\Data\Data;

abstract class Signal
{
    public const NAME = 'default';

    /**
     * @var float Value between 0 and 1
     */
    protected float $score = 1.00;

    /**
     * @param float $score Value between 0 and 1
     * @return static
     */
    public function setScore(float $score): static
    {
        $this->score = $score;
        return $this;
    }

    /**
     * @return float Value between 0 and 1
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @param Data $data
     * @return float Value between 0 and 1
     */
    protected abstract function calculateStrength(Data $data): float;

    /**
     * @param Data $data
     * @return float Value between 0 and 1
     */
    public function getStrength(Data $data): float
    {
        return $this->calculateStrength($data) * $this->score;
    }
}
