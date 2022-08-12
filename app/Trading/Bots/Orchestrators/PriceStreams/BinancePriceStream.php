<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Trading\Models\Trading;
use Binance\Websocket\Spot;
use Closure;
use Ratchet\Client\Connector as SocketClientConnector;
use React\EventLoop\LoopInterface;
use React\Socket\Connector as SocketConnector;

class BinancePriceStream extends PriceStream
{
    public function __construct()
    {
        parent::__construct('binance');
    }

    protected function getStreamNames(): array
    {
        return $this->fetchTradings()
            ->map(function (Trading $trading) {
                return sprintf('%s@kline_%s', strtolower($trading->ticker), $trading->interval);
            })
            ->all();
    }

    protected function createSocketClient(LoopInterface $loop): Spot
    {
        return new Spot(['wsConnector' => new SocketClientConnector($loop, new SocketConnector($loop))]);
    }

    /**
     * @param Spot $socketClient
     * @param Closure $onMessage
     */
    protected function listen($socketClient, Closure $onMessage)
    {
        $socketClient->combined($this->getStreamNames(), [
            'message' => $onMessage,
        ]);
    }

    protected function priceMessageExtractor(): IPriceMessageExtract
    {
        return new BinancePriceStreamMessageExtractor();
    }

    protected function pingBackTimer(): int|float
    {
        return 5 * 60;
    }

    /**
     * @param Spot $socketClient
     */
    protected function pingBack($socketClient)
    {
        $socketClient->ping();
    }
}
