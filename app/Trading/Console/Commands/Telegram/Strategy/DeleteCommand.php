<?php

namespace App\Trading\Console\Commands\Telegram\Strategy;

use App\Models\User;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\InteractsWithTarget;
use App\Trading\Console\Commands\Telegram\InteractsWithPriceStream;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;
use Illuminate\Database\Eloquent\Collection;

class DeleteCommand extends Command
{
    use InteractsWithTarget, InteractsWithPriceStream;

    public $signature = '{id?} {--all}';

    protected $description = 'Delete a strategy or all.';

    protected function findTradingStrategy(User $user): ?TradingStrategy
    {
        return is_null($id = $this->id()) ? null : (new TradingStrategyProvider())
            ->notStrict()
            ->first([
                'id' => $id,
                'user_id' => $user->id,
            ]);
    }

    /**
     * @param User $user
     * @return Collection<int, TradingStrategy>|null
     */
    protected function findTradingStrategies(User $user): ?Collection
    {
        return (new TradingStrategyProvider())->all(['user_id' => $user->id]);
    }

    protected function handling(): int
    {
        if (($user = $this->validateFindingUser()) !== false) {
            if (!is_null($strategy = $this->findTradingStrategy($user))) {
                $this->deleteStrategy($strategy);
                $this->sendConsoleNotification(sprintf('Trading strategy {#%s} was removed successfully.', $strategy->id));
            }
            elseif ($this->all()) {
                foreach ($this->findTradingStrategies($user) as $strategy) {
                    $this->deleteStrategy($strategy);
                }
                $this->sendConsoleNotification('All trading strategies were removed successfully.');
            }
            else {
                $this->sendConsoleNotification('No trading strategy was removed.');
            }
        }
        return $this->exitSuccess();
    }

    protected function deleteStrategy(TradingStrategy $strategy): void
    {
        $buyTrading = $strategy->buyTrading;
        $sellTrading = $strategy->sellTrading;

        $strategy->delete();
        $this->unsubscribePriceStream($buyTrading);
        if ($buyTrading->id !== $sellTrading->id) {
            $this->unsubscribePriceStream($sellTrading);
        }
    }
}