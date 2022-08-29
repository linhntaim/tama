<?php

namespace App\Trading\Bots\Strategies;

use App\Trading\Bots\Bot;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Pricing\SwapperFactory;
use Illuminate\Support\Collection;

class Strategy
{
    /**
     * @var Collection<int, Swap>
     */
    protected Collection $swaps;

    public function __construct(
        protected string $baseSymbol,
        protected string $quoteSymbol,
        protected bool   $backTest = false
    )
    {
        $this->swaps = collect([]);
    }

    public function setBackTest(bool $backTest = true): static
    {
        $this->backTest = $backTest;
        return $this;
    }

    protected function getBaseAmount(): float
    {
        return $this->swaps->sum(static fn(Swap $swap) => $swap->getBaseAmount());
    }

    protected function getQuoteAmount(): float
    {
        return $this->swaps->sum(static fn(Swap $swap) => $swap->getQuoteAmount());
    }

    public function trade(Bot $bot, Indication $indication): void
    {
        switch (true) {
            case $indication->getActionBuy():
                $this->buy($bot, $indication);
                break;
            case $indication->getActionSell():
                $this->sell($bot, $indication);
                break;
        }
    }

    public function buy(Bot $bot, Indication $indication): static
    {
        [$fromAmount, $toAmount] = $this->swap(
            $this->baseSymbol,
            $this->quoteSymbol,
            $this->determineBuyingAmount($indication),
            $indication
        );
        $this->swaps->push(new Swap(-$fromAmount, $toAmount));
        return $this;
    }

    protected function determineBuyingAmount(Indication $indication): float
    {
        return $this->getBaseAmount();
    }

    public function sell(Bot $bot, Indication $indication): static
    {
        [$fromAmount, $toAmount] = $this->swap(
            $this->quoteSymbol,
            $this->baseSymbol,
            $this->determineSellingAmount($indication),
            $indication
        );
        $this->swaps->push(new Swap($toAmount, -$fromAmount));
        return $this;
    }

    protected function determineSellingAmount(Indication $indication): float
    {
        return $this->getQuoteAmount();
    }

    protected function swapper()
    {
        return $this->swapper ?? $this->swapper = SwapperFactory::create($this->exchange);
    }

    /**
     * @param string $fromSymbol
     * @param string $toSymbol
     * @param float $fromAmount
     * @param Indication $indication
     * @return float[]
     */
    protected function swap(string $fromSymbol, string $toSymbol, float $fromAmount, Indication $indication): array
    {
        return [$fromAmount, $fromAmount];
    }
}
