<?php

return [
    'redis' => [
        'pubsub_connection' => env('TRADING_REDIS_PUBSUB_CONNECTION', 'default'),
    ],
    'exchange' => [
        'disables' => env('TRADING_EXCHANGE_DISABLES'),
    ],
];
