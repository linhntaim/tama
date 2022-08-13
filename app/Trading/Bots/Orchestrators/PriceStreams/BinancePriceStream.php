<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Trading\Models\Trading;
use Illuminate\Database\Eloquent\Collection;
use Ratchet\RFC6455\Messaging\Frame;
use React\EventLoop\LoopInterface;

class BinancePriceStream extends PriceStream
{
    public function __construct(LoopInterface $loop)
    {
        parent::__construct($loop, 'binance', 'wss://stream.binance.com:9443/ws', 0);
    }

    protected function createMessageExtractor(): IPriceMessageExtract
    {
        return new BinancePriceStreamMessageExtractor();
    }

    protected function subscribe()
    {
        $this->getConnection()->send(new Frame(json_encode([
            'method' => 'SET_PROPERTY',
            'params' => ['combined', true],
            'id' => $this->getId(),
        ]), true, Frame::OP_TEXT));
        parent::subscribe();
    }

    protected function subscribeTradings(Collection $tradings)
    {
        $this->getConnection()->send(new Frame(json_encode([
            'method' => 'SUBSCRIBE',
            'params' => $tradings
                ->map(function (Trading $trading) {
                    return sprintf('%s@kline_%s', strtolower($trading->ticker), $trading->interval);
                })
                ->all(),
            'id' => $this->getId(),
        ]), true, Frame::OP_TEXT));
    }

    public function subscribeTrading(string $ticker, string $interval)
    {
        $this->getConnection()->send(new Frame(json_encode([
            'method' => 'SUBSCRIBE',
            'params' => [sprintf('%s@kline_%s', strtolower($ticker), $interval)],
            'id' => $this->getId(),
        ]), true, Frame::OP_TEXT));
    }

    public function unsubscribeTrading(string $ticker, string $interval)
    {
        $this->getConnection()->send(new Frame(json_encode([
            'method' => 'UNSUBSCRIBE',
            'params' => [sprintf('%s@kline_%s', strtolower($ticker), $interval)],
            'id' => $this->getId(),
        ]), true, Frame::OP_TEXT));
    }
}
