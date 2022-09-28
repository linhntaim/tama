<?php

namespace App\Trading\Console\Commands\Telegram\Strategy;

use App\Models\User;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\InteractsWithListing;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;
use App\Trading\Models\TradingSwap;

class ListCommand extends Command
{
    use InteractsWithListing;

    public $signature = '{--q= : The keyword for searching.} {--page=1}';

    protected $description = 'List all strategies.';

    protected array $tickerPrices = [];

    protected function tickerPrice(string $exchange, string $ticker): string
    {
        $key = sprintf('%s.%s', $exchange, $ticker);
        return $this->tickerPrices[$key]
            ?? $this->tickerPrices[$key] = Exchanger::connector($exchange)->tickerPrice($ticker);
    }

    protected function handling(): int
    {
        if (($user = $this->validateFindingUser()) !== false) {
            $this->sendConsoleNotification($this->printStrategiesBySubscriber($user));
        }
        return $this->exitSuccess();
    }

    protected function printStrategiesBySubscriber(User $user): string
    {
        return $this->printList(
            (new TradingStrategyProvider())
                ->with(['buyTradings', 'sellTradings'])
                ->paginationByUser($user, $this->keyword(), 5, $this->page()),
            function (TradingStrategy $strategy) {
                return transform(
                    $strategy->firstSwap,
                    function (TradingSwap $swap) use ($strategy) {
                        $firstTrading = $strategy->buyTradings->first();
                        return implode(PHP_EOL, [
                            sprintf('{#%d}', $strategy->id),
                            sprintf('- Buy (risk=%s):', $strategy->buy_risk),
                            ...$strategy->buyTradings->map(function (Trading $trading) {
                                return sprintf('  + {#%d:%s}', $trading->id, $trading->slug);
                            })->all(),
                            sprintf('- Sell (risk=%s):', $strategy->sell_risk),
                            ...$strategy->sellTradings->map(function (Trading $trading) {
                                return sprintf('  + {#%d:%s}', $trading->id, $trading->slug);
                            })->all(),
                            sprintf(
                                '- Start: %s %s + %s %s ~ %s %s @ %s',
                                num_trim($swap->base_amount),
                                $firstTrading->base_symbol,
                                num_trim($swap->quote_amount),
                                $firstTrading->quote_symbol,
                                num_trim($beforeEquivalentQuoteAmount = $swap->equivalentQuoteAmount),
                                $firstTrading->quote_symbol,
                                $swap->getAttribute('created_at')
                            ),
                            sprintf(
                                '- Now: %s %s + %s %s ~ %s %s',
                                num_trim($strategy->baseAmount),
                                $firstTrading->base_symbol,
                                num_trim($strategy->quoteAmount),
                                $firstTrading->quote_symbol,
                                num_trim(
                                    $afterEquivalentQuoteAmount = $strategy->calculateEquivalentQuoteAmount(
                                        $this->tickerPrice(
                                            $firstTrading->exchange,
                                            $firstTrading->ticker
                                        )
                                    )
                                ),
                                $firstTrading->quote_symbol,
                            ),
                            sprintf(
                                '- Trades: %d ~ %d BUY / %d SELL',
                                $strategy->tradeSwaps->count(),
                                $strategy->buySwaps->count(),
                                $strategy->sellSwaps->count()
                            ),
                            sprintf(
                                '- Profit: %s %s ~ %s%%',
                                num_trim($profit = num_sub($afterEquivalentQuoteAmount, $beforeEquivalentQuoteAmount)),
                                $firstTrading->quote_symbol,
                                num_mul(num_div($profit, $beforeEquivalentQuoteAmount), 100, 2)
                            ),
                        ]);
                    },
                    ''
                );
            },
            'No strategies.',
            'Trading strategies:'
        );
    }
}
