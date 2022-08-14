<?php

namespace App\Trading\Bots\Orchestrators;

use App\Trading\Bots\Actions\IAction;
use App\Trading\Bots\Exchanges\Factory as ExchangeFactory;
use App\Trading\Bots\Pricing\PriceProviderFactory;
use App\Trading\Bots\Pricing\LatestPrice;
use App\Trading\Bots\Pricing\PriceProvider;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use Illuminate\Database\Eloquent\Collection;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

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

    protected function priceProvider(): PriceProvider
    {
        return PriceProviderFactory::create($this->latestPrice->getExchange());
    }

    /**
     * @return Collection<int, Trading>
     */
    protected function fetchTradings(): Collection
    {
        return (new TradingProvider())->allByHavingSubscribers(
            $this->latestPrice->getExchange(),
            $this->latestPrice->getTicker(),
            $this->latestPrice->getInterval()
        );
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function proceed()
    {
        if (ExchangeFactory::enabled($this->latestPrice->getExchange())) {
            return;
        }

        $this->priceProvider()->pushLatest($this->latestPrice);
        parent::proceed();
    }
}
