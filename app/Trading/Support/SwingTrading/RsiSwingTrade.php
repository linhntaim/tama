<?php

namespace App\Trading\Support\SwingTrading;

class RsiSwingTrade extends SwingTrade
{
    public RsiValue $divergence1;

    public RsiValue $divergence2;

    public function __construct(?string $time, ?float $price, float $strength, RsiValue $divergence1, RsiValue $divergence2)
    {
        parent::__construct($time, $price, $strength);

        $this->divergence1 = $divergence1;
        $this->divergence2 = $divergence2;
    }

    public function toArray(): array
    {
        return parent::toArray()
            + [
                'divergence_1' => $this->divergence1,
                'divergence_2' => $this->divergence2,
            ];
    }
}
