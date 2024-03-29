<?php

namespace App\Trading\Bots\Actions;

use App\Trading\Bots\Bot;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingBroadcast;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;
use App\Trading\Models\TradingSwapProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use Throwable;

class TradeAction implements IAction
{
    public function __invoke(Trading $trading, Bot $bot, TradingBroadcast $broadcast): void
    {
        foreach ((new TradingStrategyProvider())->allActiveByTrading($trading) as $strategy) {
            $this->invokeTrading($bot, $broadcast, $strategy);
        }
    }

    protected function invokeTrading(Bot $bot, TradingBroadcast $broadcast, TradingStrategy $strategy): void
    {
        $indication = $broadcast->indication;
        try {
            if ($strategy->isFake) {
                $bot->useFakeExchangeConnector();
                $bot->exchangeConnector()->setTickerPrice($bot->ticker(), $indication->getPrice());
            }
            if (!is_null($marketOrder = $bot->tradeNow(
                $strategy->user,
                $strategy->baseAmount,
                $strategy->quoteAmount,
                $strategy->buy_risk,
                $strategy->sell_risk,
                $broadcast->indication
            ))) {
                (new TradingSwapProvider)->createWithAttributes(
                    [
                        'trading_strategy_id' => $strategy->id,
                        'trading_broadcast_id' => $broadcast->id,
                        'time' => date(DATE_DATABASE, $marketOrder->getTime()),
                        'price' => $marketOrder->getPrice(),
                        'exchange_order' => $marketOrder,
                    ]
                    + ($marketOrder->buy() ? [
                        'base_amount' => $marketOrder->getToAmount(),
                        'quote_amount' => num_neg($marketOrder->getFromAmount()),
                    ] : [
                        'base_amount' => num_neg($marketOrder->getFromAmount()),
                        'quote_amount' => $marketOrder->getToAmount(),
                    ])
                );

                $label = sprintf('STRATEGY #%d: %s %s %s', $strategy->id, $indication->getAction(), $bot->ticker(), $bot->interval());
                ConsoleNotification::send(
                    $strategy->user->load('socials'),
                    implode(PHP_EOL, [
                        $label,
                        str_repeat('‾', strlen($label)),
                        'TRADE:',
                        '*************************',
                        $indication->getActionBuy()
                            ? sprintf('BOUGHT %s %s from %s %s at %s',
                            num_trim($marketOrder->getToAmount()),
                            $bot->baseSymbol(),
                            num_trim($marketOrder->getFromAmount()),
                            $bot->quoteSymbol(),
                            $marketOrder->getPrice())
                            : sprintf(
                            'SOLD %s %s to %s %s at %s',
                            num_trim($marketOrder->getFromAmount()),
                            $bot->baseSymbol(),
                            num_trim($marketOrder->getToAmount()),
                            $bot->quoteSymbol(),
                            $marketOrder->getPrice()),
                        '',
                        'INDICATION:',
                        '*************************',
                        $bot->reportNow($indication),
                    ])
                );
            }
            $bot->removeFakeExchangeConnector();
        }
        catch (Throwable $exception) {
            report($exception);
        }
    }
}
