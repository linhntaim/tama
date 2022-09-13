<?php

namespace App\Trading\Console\Commands\Telegram;

class TradesCommand extends Command
{
    use InteractsWithTrading;

    public $signature = '{--bot=oscillating_bot} {--exchange=binance} {--ticker=BTCUSDT} {--interval=1d} {--bot-options=} {--latest=1}';

    protected $description = 'Get latest possible tradings.';

    protected function latest(): int
    {
        return with((int)($this->option('latest') ?? 1), static function (int $latest) {
            return $latest > 0 && $latest <= 5 ? $latest : 1;
        });
    }

    protected function handling(): int
    {
        if ($this->validateInputs()) {
            $this->sendConsoleNotification($this->report() ?: 'No trade found.');
        }
        return $this->exitSuccess();
    }

    protected function report(): ?string
    {
        return $this->createBot()->report($this->latest());
    }
}
