<?php

function trading_cfg(?string $key = null, mixed $default = null)
{
    $key = is_null($key) ? 'trading' : 'trading.' . $key;
    return config($key, $default);
}

function trading_cfg_redis_pubsub_connection(): string
{
    return trading_cfg('redis.pubsub_connection', 'default');
}

function trading_cfg_exchange_disables(): array
{
    return array_filter(
        array_map(function ($exchange) {
            return strlen(($exchange == trim($exchange))) == 0 ? null : $exchange;
        }, explode(',', trading_cfg('exchange.disables') ?: ''))
    );
}
