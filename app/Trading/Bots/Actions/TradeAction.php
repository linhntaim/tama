<?php

namespace App\Trading\Bots\Actions;

use App\Trading\Bots\Bot;
use App\Trading\Bots\Data\Indication;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;
use App\Trading\Models\TradingSwapProvider;
use Throwable;

class TradeAction implements IAction
{
    public function __invoke(Trading $trading, Bot $bot, Indication $indication): void
    {
        if ($indication->getActionNeutral()) {
            return;
        }

        foreach ((new TradingStrategyProvider())->allByTrading($trading) as $strategy) {
            $this->invokeTrading($trading, $strategy, $bot, $indication);
        }
    }

    protected function invokeTrading(Trading $trading, TradingStrategy $strategy, Bot $bot, Indication $indication): void
    {
        try {
            if (!is_null($trade = $bot->tradeNow(
                $strategy->baseAmount,
                $strategy->quoteAmount,
                $strategy->buy_risk,
                $strategy->sell_risk,
                $indication
            ))) {
                (new TradingSwapProvider)->createWithAttributes([
                    'trading_strategy_id' => $strategy->id,
                    'trading_id' => $trading->id,
                    'base_amount' => $trade->getBaseAmount(),
                    'quote_amount' => $trade->getQuoteAmount(),
                ]);
            }
        }
        catch (Throwable $exception) {
            report($exception);
        }
    }
}
