<?php

namespace App\Trading\Console\Commands\Telegram\Trading;

use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\InteractsWithTarget;
use App\Trading\Console\Commands\Telegram\InteractsWithTrading;

class UnsubscribeCommand extends Command
{
    use InteractsWithTarget, InteractsWithTrading;

    public $signature = '{id? : The ID or slug of the trading.} {--bot=} {--exchange=} {--ticker=} {--interval=} {--all}';

    protected $description = 'Unsubscribe tradings.';

    protected function handling(): int
    {
        if (($user = $this->validateFindingUser()) !== false) {
            if (!is_null($trading = $this->findTrading($this->id(), $user))) {
                $this->unsubscribe($user, $trading);
                $this->sendConsoleNotification(sprintf('Subscription to the trading {#%s:%s} was removed successfully.', $trading->id, $trading->slug));
            }
            elseif ($this->all()) {
                foreach ($user->tradings as $trading) {
                    $this->unsubscribe($user, $trading);
                }
                $this->sendConsoleNotification('Subscriptions to all tradings were removed successfully.');
            }
            elseif (!is_null($tradings = $this->findTradings($user)) && ($count = $tradings->count()) > 0) {
                foreach ($tradings as $trading) {
                    $this->unsubscribe($user, $trading);
                }
                if ($count === 1) {
                    $trading = $tradings->first();
                    $this->sendConsoleNotification(sprintf('Subscription to the trading {#%s:%s} was removed successfully.', $trading->id, $trading->slug));
                }
                else {
                    $this->sendConsoleNotification(sprintf('Subscriptions to %d tradings were removed successfully.', $count));
                }
            }
            else {
                $this->sendConsoleNotification('No subscription was removed.');
            }
        }
        return $this->exitSuccess();
    }
}