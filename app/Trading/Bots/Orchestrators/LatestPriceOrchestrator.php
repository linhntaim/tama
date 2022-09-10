<?php

namespace App\Trading\Bots\Orchestrators;

use App\Trading\Bots\Actions\IAction;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Bots\Exchanges\LatestPrice;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use Illuminate\Database\Eloquent\Collection;

class LatestPriceOrchestrator extends Orchestrator
{
    /**
     * @param LatestPrice $latestPrice
     * @param IAction[] $actions
     */
    public function __construct(
        protected LatestPrice $latestPrice,
        array                 $actions
    )
    {
        parent::__construct($actions);
    }

    /**
     * @return Collection<int, Trading>
     */
    protected function fetchTradings(): Collection
    {
        return (new TradingProvider())->allByRunning(
            $this->latestPrice->getExchange(),
            $this->latestPrice->getTicker(),
            $this->latestPrice->getInterval()
        );
    }

    public function proceed(): void
    {
        $exchange = $this->latestPrice->getExchange();
        if (Exchanger::available($exchange)) {
            Exchanger::connector($exchange)->pushLatestPrice($this->latestPrice);
            parent::proceed();
        }
    }
}
