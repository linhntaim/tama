<?php

namespace App\Console\Commands\Coin;

use App\Jobs\CoinGeckoIdentificationJob;
use App\Jobs\CoinMarketCapIdentificationJob;
use App\Support\Console\Commands\Command;

class IdentificationCommand extends Command
{
    protected function handling(): int
    {
        CoinGeckoIdentificationJob::dispatch();
        CoinMarketCapIdentificationJob::dispatch();
        return $this->exitSuccess();
    }
}