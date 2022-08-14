<?php

namespace App\Trading\Bots\Orchestrators;

use App\Trading\Bots\Exchanges\Factory as ExchangeFactory;
use App\Trading\Bots\Orchestrators\PriceStreams\PriceStream;
use App\Trading\Bots\Orchestrators\PriceStreams\Factory as PriceStreamFactory;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Redis\PubSubConnection as RedisPubSubConnection;
use App\Trading\Redis\PubSubManager as RedisPubSubManager;
use Illuminate\Database\Eloquent\Collection;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

class PriceStreamOrchestrator extends Orchestrator
{
    protected LoopInterface $loop;

    /**
     * @var PriceStream[]
     */
    protected array $streams;

    public function __construct()
    {
        $this->loop = Loop::get();
        $this->streams = [];
        parent::__construct([]);
    }

    /**
     * @return Collection<int, Trading>
     */
    protected function fetchTradings(): Collection
    {
        return (new TradingProvider())
            ->select('exchange')
            ->group('exchange')
            ->allByHavingSubscribers(ExchangeFactory::enables());
    }

    protected function subscribeTrading(array $trading)
    {
        if (!is_null($exchange = $trading['exchange'] ?? null)
            && !is_null($ticker = $trading['ticker'] ?? null)
            && !is_null($interval = $trading['interval'] ?? null)) {
            if (isset($this->streams[$exchange])) { // exchange stream exists
                $this->streams[$exchange]->subscribeTrading($ticker, $interval);
            }
            elseif (ExchangeFactory::enabled($exchange)) {
                // create new exchange stream
                // current trading will be included in starting subscription inside the stream
                $this->streams[$exchange] = PriceStreamFactory::create($this->loop, $exchange)();
            }
        }
    }

    protected function unsubscribeTrading(array $trading)
    {
        if (!is_null($exchange = $trading['exchange'] ?? null)
            && !is_null($ticker = $trading['ticker'] ?? null)
            && !is_null($interval = $trading['interval'] ?? null)
            && isset($this->streams[$exchange])) {
            $this->streams[$exchange]->unsubscribeTrading($ticker, $interval);
        }
    }

    public function proceed()
    {
        $this->streams = $this->fetchTradings()
            ->keyBy('exchange')
            ->map(fn(Trading $trading) => PriceStreamFactory::create($this->loop, $trading->exchange)())
            ->all();

        (new RedisPubSubManager())->create($this->loop, trading_cfg_redis_pubsub_connection())
            ->then(function (RedisPubSubConnection $connection) {
                $connection->subscribe('price-stream:stop', function () {
                    $this->loop->stop();
                });
                $connection->subscribe('price-stream:subscribe', function (string $message) {
                    $this->subscribeTrading(json_decode_array($message) ?: []);
                });
                $connection->subscribe('price-stream:unsubscribe', function (string $message) {
                    $this->unsubscribeTrading(json_decode_array($message) ?: []);
                });
            });

        $this->loop->run();
    }
}
