<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Actions\IAction;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use Illuminate\Database\Eloquent\Collection;

class BotOrchestrator
{
    /**
     * @return Collection|Trading[]
     */
    protected function fetchTradings(): Collection|array
    {
        return (new TradingProvider())->allByHavingSubscribers();
    }

    /**
     * @param IAction[] $actions
     */
    public function broadcast(array $actions)
    {
        foreach ($this->fetchTradings() as $trading) {
            $this->broadcastTrading($trading, $actions);
        }
    }

    /**
     * @param Trading $trading
     * @param IAction[] $actions
     */
    protected function broadcastTrading(Trading $trading, array $actions)
    {
        (new BotBroadcaster($trading, $actions))->broadcast();
    }
}
