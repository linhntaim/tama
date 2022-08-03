<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Bots\OscillatingBot;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class TradesCommand extends Command
{
    public $signature = '{--latest=1} {--exchange=binance} {--ticker=BTCUSDT} {--interval=1d} {--oscillator-name=rsi}';

    protected function latest(): int
    {
        return modify((int)($this->option('latest') ?? 1), function (int $latest) {
            return $latest > 0 ? $latest : 1;
        });
    }

    protected function exchange(): string
    {
        return $this->option('exchange') ?? 'binance';
    }

    protected function ticker(): string
    {
        return $this->option('ticker') ?? 'BTCUSDT';
    }

    protected function interval(): string
    {
        return $this->option('interval') ?? '1d';
    }

    protected function oscillatorName(): string
    {
        return $this->option('oscillator-name') ?? 'rsi';
    }

    protected function oscillatorOptions(): array
    {
        return [];
    }

    protected function describeArray(array $array, $level = 0, $parentIsAssoc = false): string
    {
        $output = '';
        $first = true;
        foreach ($array as $key => $item) {
            if (is_int($key)) {
                if (is_array($item)) {
                    $output .= $this->describeArray($item, !$parentIsAssoc ? $level + 1 : $level);
                }
                else {
                    $output .= PHP_EOL . str_repeat(' ', $level) . $item;
                }
            }
            else {
                if (is_array($item)) {
                    $output .= PHP_EOL . str_repeat(' ', $level) . $key . ':';
                    $output .= $this->describeArray($item, $level + 1, true);
                }
                else {
                    $output .= PHP_EOL . ($first && !$parentIsAssoc ? (str_repeat(' ', $level - 1)) . '-' : str_repeat(' ', $level)) . $key . ': ' . $item;
                }
            }
            $first = false;
        }
        return $output;
    }

    protected function describe(array $indicates): string
    {
        return implode(
            PHP_EOL . str_repeat('=', 30) . PHP_EOL,
            array_map(
                function ($signal) {
                    return sprintf(
                            '%s signal at %s when price is %s.',
                            $signal['value'] == -1 ? 'Buy' : 'Sell',
                            $signal['time'],
                            $signal['price'],
                        )
                        . PHP_EOL . 'Meta:'
                        . $this->describeArray($signal['meta']);
                },
                $indicates
            )
        );
    }

    protected function handling(): int
    {
        ConsoleNotification::send(
            new TelegramUpdateNotifiable($this->telegramUpdate),
            $this->describe(
                array_slice(
                    array_reverse(
                        (new OscillatingBot([
                            'exchange' => $this->exchange(),
                            'ticker' => $this->ticker(),
                            'interval' => $this->interval(),
                            'oscillator' => [
                                'name' => $this->oscillatorName(),
                                'options' => $this->oscillatorOptions(),
                            ],
                        ]))->indicate()
                    ),
                    0,
                    $this->latest()
                )
            )
        );
        return $this->exitSuccess();
    }
}
