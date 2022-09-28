<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use Illuminate\Database\Eloquent\Collection;

trait InteractsWithTradings
{
    protected function buyTradingIds(): array
    {
        return explode(',', $this->argument('buy_trading_ids'));
    }

    protected function sellTradingIds(): ?array
    {
        return transform($this->argument('sell_trading_ids'), static fn($id): array => explode(',', $id));
    }

    /**
     * @return Collection<int, Trading>[]|false
     */
    protected function validateTradings(): array|false
    {
        $tradingProvider = new TradingProvider();
        $buyTradings = $tradingProvider->allByKeys($this->buyTradingIds());
        if ($buyTradings->count() === 0) {
            $this->sendConsoleNotification('Buy tradings does not exist.');
            return false;
        }
        $sellTradings = is_null($sellTradingId = $this->sellTradingIds())
            ? $buyTradings : $tradingProvider->allByKeys($sellTradingId);
        if ($sellTradings->count() === 0) {
            $this->sendConsoleNotification('Sell tradings does not exist.');
            return false;
        }

        $tradings = $buyTradings->merge($sellTradings);
        $firstTrading = $tradings->shift();
        foreach ($tradings as $trading) {
            if ($trading->exchange !== $firstTrading->exchange
                || $trading->ticker !== $firstTrading->ticker) {
                $this->sendConsoleNotification('Tradings must have the same exchange and ticker.');
                return false;
            }
        }

        return [$buyTradings, $sellTradings];
    }
}