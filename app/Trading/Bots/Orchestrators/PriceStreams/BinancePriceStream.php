<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use Binance\Websocket\Spot;
use Ratchet\Client\Connector as SocketClientConnector;
use Ratchet\RFC6455\Messaging\Message;
use React\EventLoop\LoopInterface;
use React\Socket\Connector as SocketConnector;

class BinancePriceStream extends PriceStream
{
    protected function getStreamNames(): array
    {
        return (new TradingProvider())
            ->allByHavingSubscribers($this->exchange)
            ->map(function (Trading $trading) {
                return sprintf('%s@kline_%s', strtolower($trading->ticker), $trading->interval);
            })
            ->all();
    }

    public function __invoke(LoopInterface $loop)
    {
        $client = new Spot(['wsConnector' => new SocketClientConnector($loop, new SocketConnector($loop))]);
        $client->combined(
            $this->getStreamNames(),
            [
                'message' => function ($conn, Message $message) {
                    $this->proceedMessage(new BinancePriceStreamMessageExtractor(), $message);
                },
            ]
        );
        $loop->addPeriodicTimer(5 * 60, function () use ($client) {
            $client->ping();
        });
    }
}
