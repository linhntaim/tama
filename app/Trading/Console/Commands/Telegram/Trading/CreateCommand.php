<?php

namespace App\Trading\Console\Commands\Telegram\Trading;

use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\InteractsWithTrading;

class CreateCommand extends Command
{
    use InteractsWithTrading;

    public $signature = '{--bot=oscillating_bot} {--exchange=binance} {--ticker=BTCUSDT} {--interval=1d} {--bot-options=}';

    protected $description = 'Create a trading.';

    protected function handling(): int
    {
        if ($this->validateInputs()) {
            if (($tickers = $this->tickers()) !== false) {
                if (count($tickers) > 0) {
                    foreach ($tickers as $ticker) {
                        $this->createTrading([
                            'ticker' => $ticker->getSymbol(),
                            'base_symbol' => $ticker->getBaseSymbol(),
                            'quote_symbol' => $ticker->getQuoteSymbol(),
                            'safe_ticker' => true,
                        ]);
                    }
                    $this->sendConsoleNotification(sprintf('%s trading(s) were created successfully.', $tickers->count()));
                }
                else {
                    $this->sendConsoleNotification('No trading was created.');
                }
            }
            else {
                $trading = $this->createTrading();
                $this->sendConsoleNotification(sprintf('Trading {#%s:%s} was created successfully.', $trading->id, $trading->slug));
            }
        }
        return $this->exitSuccess();
    }
}
