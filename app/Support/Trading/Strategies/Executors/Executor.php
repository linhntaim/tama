<?php

namespace App\Support\Trading\Strategies\Executors;

use App\Support\Trading\Strategies\Data\Data;
use App\Support\Trading\Strategies\DataServices\DataService;
use App\Support\Trading\Strategies\DataServices\DataServiceFactory;
use App\Support\Trading\Strategies\Model\Strategy;
use App\Support\Trading\Strategies\Signals\BearishSignal;
use App\Support\Trading\Strategies\Signals\BullishSignal;
use App\Support\Trading\Strategies\Signals\Signal;

abstract class Executor
{
    public const NAME = 'default';

    protected float $fund;

    protected float $sellRisk;

    protected float $buyRisk;

    protected string $baseSymbol;

    protected string $quoteSymbol;

    protected string $interval;

    /**
     * @var array|BullishSignal[]
     */
    protected array $bullishSignals = [];

    /**
     * @var array|BearishSignal[]
     */
    protected array $bearishSignals = [];

    protected DataService $dataService;

    protected Strategy $strategy;

    public function __construct(Strategy $strategy)
    {
        $this->strategy = $strategy;
        $this->fund = $strategy->fund;
        $this->buyRisk = $strategy->buy_risk;
        $this->sellRisk = $strategy->sell_risk;
        $this->baseSymbol = $strategy->base_symbol;
        $this->quoteSymbol = $strategy->quote_symbol;
        $this->interval = $strategy->interval;
        $this->dataService = DataServiceFactory::create($strategy->service);
    }

    public function prices(int $limit = 1000): Data
    {
        return $this->dataService->getPrices($this->baseSymbol, $this->quoteSymbol, $this->interval, $limit);
    }

    public function baseAmount(float $percentage = 1.00): float
    {
        return $this->dataService->getAmount($this->baseSymbol) * $percentage;
    }

    public function quoteAmount(float $percentage = 1.00): float
    {
        return $this->dataService->getAmount($this->quoteSymbol) * $percentage;
    }

    public function addBullishSignal(BullishSignal $bullishSignal, float $score = 1.00)
    {
        $this->bullishSignals[] = $bullishSignal->setScore($score);
    }

    public function addBearishSignal(BearishSignal $bearishSignal, float $score = 1.00)
    {
        $this->bearishSignals[] = $bearishSignal->setScore($score);
    }

    /**
     * @param array|Signal[] $signals
     * @return float
     */
    protected function averageStrength(array $signals): float
    {
        $scores = 0;
        $strengths = 0;
        $prices = $this->prices();
        foreach ($signals as $signal) {
            $scores += $signal->getScore();
            $strengths += $signal->getStrength($prices);
        }
        return $scores ? $strengths / $scores : 0;
    }

    protected function buyMinStrength(): float
    {
        return 1 - $this->buyRisk;
    }

    protected function buyOk(float $strength): bool
    {
        return $strength >= $this->buyMinStrength();
    }

    /**
     * @param float $strength Value is between 0 and 1
     * @return float Amount to buy
     */
    protected function buyAmount(float $strength): float
    {
        return $this->quoteAmount($this->buyPercentage($strength));
    }

    /**
     * @param float $strength Value is between 0 and 1
     * @return float Value is between 0 and 1
     */
    protected function buyPercentage(float $strength): float
    {
        return 1.00;
    }

    /**
     * @param float $strength Value between 0 and 1
     * @return float Amount to buy
     */
    protected function shouldBuy(float $strength): float
    {
        return $this->buyOk($strength) ? $this->buyAmount($strength) : 0;
    }

    public function buy()
    {
        if ($amount = $this->shouldBuy($this->averageStrength($this->bullishSignals))) {
            $this->buying($amount);
        }
    }

    /**
     * @param float $amount Value is greater than 0
     */
    protected function buying(float $amount)
    {
        $this->dataService->buy($this->quoteSymbol, $amount);
    }

    protected function sellMinStrength(): float
    {
        return $this->buyRisk;
    }

    protected function sellOk(float $strength): bool
    {
        return $strength >= $this->sellMinStrength();
    }

    /**
     * @param float $strength Value is between 0 and 1
     * @return float Amount to sell
     */
    protected function sellAmount(float $strength): float
    {
        return $this->baseAmount($this->sellPercentage($strength));
    }

    /**
     * @param float $strength Value is between 0 and 1
     * @return float Value is between 0 and 1
     */
    protected function sellPercentage(float $strength): float
    {
        return 1.00;
    }

    /**
     * @param float $strength Value between 0 and 1
     * @return float Amount to sell
     */
    protected function shouldSell(float $strength): float
    {
        return $this->sellOk($strength) ? $this->sellAmount($strength) : 0;
    }

    public function sell()
    {
        if ($amount = $this->shouldSell($this->averageStrength($this->bullishSignals))) {
            $this->selling($amount);
        }
    }

    /**
     * @param float $amount Value is greater than 0
     */
    protected function selling(float $amount)
    {
        $this->dataService->sell($this->baseSymbol, $amount);
    }
}
