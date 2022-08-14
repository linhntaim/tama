<?php

namespace App\Trading\Bots\Orchestrators;

use App\Trading\Bots\Actions\IAction;
use App\Trading\Bots\BotBroadcaster;
use App\Trading\Bots\Exchanges\Factory as ExchangeFactory;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class Orchestrator
{
    /**
     * @param IAction[] $actions
     */
    public function __construct(
        protected array $actions
    )
    {
    }

    /**
     * @return Collection<int, Trading>
     */
    protected function fetchTradings(): Collection
    {
        return (new TradingProvider())->allByHavingSubscribers(ExchangeFactory::enables());
    }

    public function proceed()
    {
        $this->broadcast();
    }

    protected function broadcast()
    {
        foreach ($this->fetchTradings() as $trading) {
            $this->broadcastTrading($trading);
        }
    }

    protected function broadcastTrading(Trading $trading)
    {
        try {
            (new BotBroadcaster($trading, $this->actions))->broadcast();
        }
        catch (Throwable $exception) {
            report($exception);
        }
    }
}
