<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Trading\Bots\Exchanges\Binance;
use App\Trading\Models\Trading;
use Illuminate\Database\Eloquent\Collection;
use JsonException;
use React\EventLoop\LoopInterface;

class BinancePriceStream extends PriceStream
{
    public function __construct(LoopInterface $loop)
    {
        parent::__construct($loop, Binance::NAME, 'wss://stream.binance.com:9443/ws', 0);
    }

    protected function createMessageExtractor(): IPriceMessageExtract
    {
        return new BinancePriceStreamMessageExtractor();
    }

    /**
     * @throws JsonException
     */
    protected function call(string $method, array $params): void
    {
        $this->send([
            'method' => $method,
            'params' => $params,
            'id' => $this->getId(),
        ]);
    }

    /**
     * @throws JsonException
     */
    protected function subscribe(): void
    {
        $this->call('SET_PROPERTY', ['combined', true]);
        parent::subscribe();
    }

    /**
     * @throws JsonException
     */
    protected function subscribeTradings(Collection $tradings): void
    {
        $this->call(
            'SUBSCRIBE',
            $tradings
                ->map(function (Trading $trading) {
                    return sprintf('%s@kline_%s', strtolower($trading->ticker), $trading->interval);
                })
                ->values()
                ->all()
        );
    }

    /**
     * @throws JsonException
     */
    public function subscribeTrading(string $ticker, string $interval): void
    {
        $this->call('SUBSCRIBE', [sprintf('%s@kline_%s', strtolower($ticker), $interval)]);
    }

    /**
     * @throws JsonException
     */
    public function unsubscribeTrading(string $ticker, string $interval): void
    {
        $this->call('UNSUBSCRIBE', [sprintf('%s@kline_%s', strtolower($ticker), $interval)]);
    }
}
