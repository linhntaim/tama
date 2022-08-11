<?php

namespace App\Trading\Bots\Orchestrators;

use App\Trading\Bots\Orchestrators\PriceStreams\PriceStream;
use App\Trading\Bots\Orchestrators\PriceStreams\Factory as PriceStreamFactory;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use Illuminate\Database\Eloquent\Collection;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

class PriceStreamOrchestrator extends Orchestrator
{
    protected LoopInterface $loop;

    public function __construct()
    {
        $this->loop = Loop::get();
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

    /**
     * @return PriceStream[]
     */
    protected function getPriceStreams(): array
    {
        return $this->fetchTradings()
            ->map(fn(Trading $trading) => PriceStreamFactory::create($trading->exchange))
            ->all();
    }

    public function proceed()
    {
        foreach ($this->getPriceStreams() as $priceStream) {
            $priceStream($this->loop);
        }
        $this->loop->run();
    }
}
