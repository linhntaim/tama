<?php

namespace App\Trading\Console\Commands\Coin;

use App\Support\Console\Commands\Command;
use App\Trading\Jobs\CoinGeckoIdentificationJob;
use App\Trading\Jobs\CoinMarketCapIdentificationJob;

class IdentificationCommand extends Command
{
    protected function handling(): int
    {
        CoinGeckoIdentificationJob::dispatch();
        CoinMarketCapIdentificationJob::dispatch();
        return $this->exitSuccess();
    }
}
