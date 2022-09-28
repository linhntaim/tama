<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Models\Trading;
use Illuminate\Redis\Connections\Connection as RedisConnection;
use Illuminate\Support\Facades\Redis;

trait InteractsWithPriceStream
{
    protected RedisConnection $redis;

    protected function redis(): RedisConnection
    {
        return $this->redis ?? $this->redis = Redis::connection(trading_cfg_redis_pubsub_connection());
    }

    protected function subscribePriceStream(Trading $trading): void
    {
        $this->redis()->publish('price-stream:subscribe', json_encode_readable([
            'exchange' => $trading->exchange,
            'ticker' => $trading->ticker,
            'interval' => $trading->interval,
        ]));
    }

    protected function unsubscribePriceStream(Trading $trading): void
    {
        if ($trading->subscribers()->count()) {
            $this->redis()->publish('price-stream:unsubscribe', json_encode_readable([
                'exchange' => $trading->exchange,
                'ticker' => $trading->ticker,
                'interval' => $trading->interval,
            ]));
        }
    }
}