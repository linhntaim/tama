<?php

namespace App\Trading\Console\Commands\Orchestration;

use App\Support\Console\Commands\Command;
use Illuminate\Support\Facades\Redis;

class RestartPriceStreamCommand extends Command
{
    protected function handling(): int
    {
        Redis::connection(trading_cfg_redis_pubsub_connection())->publish('price-stream:stop', '');
        return $this->exitSuccess();
    }
}
