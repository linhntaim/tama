<?php

namespace App\Events;

use App\Support\Events\Event;

class CoinIdentificationEvent extends Event
{
    public string $symbol;

    public string $type;

    public float $circulatingSupply;

    public ?float $totalSupply = null;

    public ?float $maxSupply = null;

    public function __construct(
        string $symbol,
        string $type,
        float  $circulatingSupply,
        ?float $totalSupply,
        ?float $maxSupply,
    )
    {
        parent::__construct();

        $this->symbol = $symbol;
        $this->type = $type;
        $this->circulatingSupply = $circulatingSupply;
        $this->totalSupply = $totalSupply;
        $this->maxSupply = $maxSupply;
    }
}