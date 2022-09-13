<?php

namespace App\Trading\Console\Commands\Telegram;

class SubscribeCommand extends Command
{
    use InteractsWithTrading;

    public $signature = '{--bot=oscillating_bot} {--exchange=binance} {--ticker=BTCUSDT} {--interval=1d} {--bot-options=}';

    protected $description = 'Subscribe a trading.';

    protected function handling(): int
    {
        if ($this->validateInputs() && ($user = $this->validateCreatingUser()) !== false) {
            if (($tickers = $this->tickers()) !== false) {
                if (count($tickers) > 0) {
                    foreach ($tickers as $ticker) {
                        $this->subscribe($user, $this->createTrading([
                            'ticker' => $ticker->getSymbol(),
                            'base_symbol' => $ticker->getBaseSymbol(),
                            'quote_symbol' => $ticker->getQuoteSymbol(),
                            'safe_ticker' => true,
                        ]));
                    }
                    $this->sendConsoleNotification(sprintf('Subscriptions to %s trading(s) were created successfully.', $tickers->count()));
                }
                else {
                    $this->sendConsoleNotification('No subscription was created.');
                }
            }
            else {
                $this->subscribe($user, $trading = $this->createTrading());
                $this->sendConsoleNotification(sprintf('Subscription to the trading {#%s:%s} was created successfully.', $trading->id, $trading->slug));
            }
        }
        return $this->exitSuccess();
    }
}
