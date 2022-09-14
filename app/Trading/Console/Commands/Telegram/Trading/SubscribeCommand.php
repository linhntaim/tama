<?php

namespace App\Trading\Console\Commands\Telegram\Trading;

use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\InteractsWithTarget;
use App\Trading\Console\Commands\Telegram\InteractsWithTrading;
use App\Trading\Models\Trading;

class SubscribeCommand extends Command
{
    use InteractsWithTarget, InteractsWithTrading;

    public $signature = '{id?} {--bot=} {--exchange=} {--ticker=} {--interval=}';

    protected $description = 'Subscribe one or many tradings.';

    protected function handling(): int
    {
        if (($user = $this->validateCreatingUser()) !== false) {
            if (!is_null($id = $this->id())) {
                if (!is_null($trading = $this->findTrading($id))) {
                    $this->sendConsoleNotification('Trading not found.');
                }
                else {
                    $this->subscribe($user, $trading);
                    $this->sendConsoleNotification(sprintf('Subscription to the trading {#%s:%s} was created successfully.', $trading->id, $trading->slug));
                }
            }
            elseif (!is_null($tradings = $this->findTradings()) && ($count = $tradings->count()) > 0) {
                foreach ($tradings as $trading) {
                    $this->subscribe($user, $trading);
                }
                if ($count === 1) {
                    take(
                        $tradings->first(),
                        fn(Trading $trading) => $this->sendConsoleNotification(
                            sprintf('Subscription to the trading {#%s:%s} was created successfully.', $trading->id, $trading->slug)
                        )
                    );
                }
                else {
                    $this->sendConsoleNotification(sprintf('Subscriptions to %d tradings were created successfully.', $count));
                }
            }
            else {
                $this->sendConsoleNotification('No subscription was created.');
            }
        }
        return $this->exitSuccess();
    }
}