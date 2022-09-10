<?php

namespace App\Trading\Console\Commands\Telegram\Strategy;

use App\Models\User;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\FindUser;
use App\Trading\Console\Commands\Telegram\PrintList;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class ListCommand extends Command
{
    use FindUser, PrintList;

    public $signature = '{--q= : The keyword for searching.} {--page=1}';

    protected $description = 'List all strategies.';

    protected function keyword(): ?string
    {
        return $this->option('q');
    }

    protected function page(): int
    {
        return with((int)($this->option('page') ?? 1), static fn($page) => $page <= 0 ? 1 : $page);
    }

    protected function handling(): int
    {
        ConsoleNotification::send(
            new TelegramUpdateNotifiable($this->telegramUpdate),
            !is_null($user = $this->findUser())
                ? $this->printStrategiesBySubscriber($user)
                : 'No subscriptions.'
        );
        return $this->exitSuccess();
    }

    protected function printStrategiesBySubscriber(User $user): string
    {
        return $this->printList(
            (new TradingStrategyProvider())->paginationByUser($user, $this->keyword(), 10, $this->page()),
            function (TradingStrategy $strategy) {
                $currentPrice = Exchanger::connector($strategy->buyTrading->exchange)->tickerPrice($strategy->buyTrading->ticker);
                $swap = $strategy->orderedSwaps->first();
                return implode(PHP_EOL, [
                    sprintf('{#%d}', $strategy->id),
                    sprintf('- Buy: {#%d:%s} risk=%s', $strategy->buyTrading->id, $strategy->buyTrading->slug, $strategy->buy_risk),
                    sprintf('- Sell: {#%d:%s} risk=%s', $strategy->sellTrading->id, $strategy->buyTrading->slug, $strategy->sell_risk),
                    sprintf(
                        '- Starting amount: %s%s + %s%s ~ %s%s',
                        num_str($swap->base_amount),
                        $strategy->buyTrading->base_symbol,
                        num_str($swap->quote_amount),
                        $strategy->buyTrading->quote_symbol,
                        num_add($swap->price * $swap->base_amount, $swap->quote_amount),
                        $strategy->buyTrading->quote_symbol,
                    ),
                    sprintf(
                        '- Curren amount: %s%s + %s%s ~ %s%s',
                        num_str($strategy->baseAmount),
                        $strategy->buyTrading->base_symbol,
                        num_str($strategy->quoteAmount),
                        $strategy->buyTrading->quote_symbol,
                        num_add($currentPrice * $strategy->baseAmount, $strategy->quoteAmount),
                        $strategy->buyTrading->quote_symbol,
                    ),
                ]);
            },
            'No strategies.',
            'Trading strategies:'
        );
    }
}
