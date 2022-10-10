<?php

namespace App\Trading\Console\Commands\Strategy\Test;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Bots\Tests\BotTest;
use App\Trading\Bots\Tests\Reports\IReportTest;
use App\Trading\Bots\Tests\Reports\MonthlyPlainTextReportTest;

class QuickCommand extends Command
{
    public $signature = '{--exchange=binance} {--ticker=BTCUSDT} {--interval=1h} {--start-time=4Y} {--end-time=}';

    protected IReportTest $reporter;

    protected function exchange(): string
    {
        return strtolower($this->option('exchange'));
    }

    protected function ticker(): string
    {
        return strtoupper($this->option('ticker'));
    }

    protected function interval(): string
    {
        return $this->option('interval');
    }

    protected function startTime(): ?string
    {
        return $this->option('start-time');
    }

    protected function endTime(): ?string
    {
        return $this->option('end-time');
    }

    protected function handling(): int
    {
        $this->reporter = new MonthlyPlainTextReportTest();
        $this->out(implode(PHP_EOL, [
            sprintf('%s - %s', $this->exchange(), $this->ticker()),
            str_repeat('‾', 25),
            $this->report(),
        ]));
        return $this->exitSuccess();
    }

    protected function report(): string
    {
        return (new BotTest(
            0.0,
            500.0,
            0.0,
            0.0,
            [
                [ // bot buy 1
                    'name' => 'oscillating_bot',
                    'options' => [
                        'exchange' => $this->exchange(),
                        'ticker' => $this->ticker(),
                        'interval' => $this->interval(),
                        'oscillator' => [
                            'name' => RsiOscillator::NAME,
                        ],
                    ],
                ],
            ],
        ))
            ->test($this->startTime(), $this->endTime())
            ->setReporter($this->reporter)
            ->report();
    }

    protected function out(string $report): void
    {
        $this->line($report);
        $this->file($report);
    }

    protected function file(string $report): void
    {
        file_put_contents(
            storage_path(sprintf('logs/%s_%s.log', $this->exchange(), $this->ticker())),
            $report
        );
        $resource = fopen(storage_path(sprintf('logs/%s.csv.log', $this->exchange())), 'a');
        fputcsv($resource, $this->reporter->getSummary());
        fclose($resource);
    }
}
