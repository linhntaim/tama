<?php

namespace App\Trading\Bots\Orchestrators;

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
            ->allByHavingSubscribers();
    }

    public function proceed()
    {
        $this->streams = $this->fetchTradings()
            ->keyBy('exchange')
            ->map(fn(Trading $trading) => PriceStreamFactory::create($this->loop, $trading->exchange)())
            ->all();

        (new RedisPubSubManager())->create($this->loop)->then(function (RedisPubSubConnection $connection) {
            $connection->subscribe('trading:subscribe', function (string $message) {
                $trading = json_decode_array($message) ?: [];
                if (!is_null($exchange = $trading['exchange'] ?? null)
                    && !is_null($ticker = $trading['ticker'] ?? null)
                    && !is_null($interval = $trading['interval'] ?? null)
                    && isset($this->streams[$exchange])) {
                    $this->streams[$exchange]->subscribeTrading($ticker, $interval);
                }
            });
            $connection->subscribe('trading:unsubscribe', function (string $message) {
                $trading = json_decode_array($message) ?: [];
                if (!is_null($exchange = $trading['exchange'] ?? null)
                    && !is_null($ticker = $trading['ticker'] ?? null)
                    && !is_null($interval = $trading['interval'] ?? null)
                    && isset($this->streams[$exchange])) {
                    $this->streams[$exchange]->unsubscribeTrading($ticker, $interval);
                }
            });
        });

        $this->loop->run();
    }
}
