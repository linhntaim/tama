<?php

namespace App\Support\Trading\Strategies;

use App\Support\Trading\Strategies\DataServices\DataServiceFactory;
use App\Support\Trading\Strategies\Executors\ExecutorFactory;
use App\Support\Trading\Strategies\Model\Strategy;
use App\Support\Trading\Strategies\Model\StrategyProvider;

class Orchestrator
{
    public function buy($user)
    {
        foreach ((new StrategyProvider())->allByUser($user)->mapToGroups(function (Strategy $strategy) {
            return [$strategy->service => $strategy];
        })->all() as $service => $strategies) {
            $this->buyWithService($service, $strategies);
        }
    }

    /**
     * @param string $service
     * @param array|Strategy[] $strategies
     */
    protected function buyWithService(string $service, array $strategies)
    {
        foreach (collect($strategies)->mapToGroups(function (Strategy $strategy) {
            return [$strategy->quote_symbol => $strategy];
        })->all() as $quoteSymbol => $strategies) {
            $this->buyWithServiceAndQuoteSymbol($service, $quoteSymbol, $strategies);
        }
    }

    /**
     * @param string $service
     * @param string $quoteSymbol
     * @param array|Strategy[] $strategies
     */
    protected function buyWithServiceAndQuoteSymbol(string $service, string $quoteSymbol, array $strategies)
    {
        DataServiceFactory::create($service);
        // TODO: validate current fund of `$quoteSymbol`
        foreach ($strategies as $strategy) {
            ExecutorFactory::create($strategy)->buy();
        }
    }

    public function sell()
    {
        foreach ((new StrategyProvider())->all() as $strategy) {
            ExecutorFactory::create($strategy)->sell();
        }
    }
}
