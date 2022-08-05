<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Reporters\PlainTextReporter;

/**
 * @property OscillatingBot $bot
 */
class OscillatingBotReporter extends PlainTextReporter
{
    protected function title(): string
    {
        return parent::title() . sprintf(' (%s)', $this->bot->oscillatorName());
    }

    protected function indicationMeta(array $meta): string
    {
        $lines = [];
        foreach ($meta as $item) {
            $lines[] = match ($item['type']) {
                'rsi' => $this->indicationRsi($item),
                default => ''
            };
        }
        return $this->lines($lines);
    }

    protected function indicationRsi(array $rsi): string
    {
        $lines = [sprintf('{RSI=%s}', $rsi['rsi'])];
        foreach ($rsi['signals'] as $signal) {
            $lines[] = $this->indicationRsiSignal($signal);
        }

        return $this->lines($lines);
    }

    protected function indicationRsiSignal(array $signal): string
    {
        $lines = [sprintf('#signal:%s:%s', $signal['type'], $signal['strength'])];
        switch ($signal['type']) {
            case 'bullish_divergence':
            case 'bearish_divergence':
                $lines[] = sprintf('D1: [%s] (rsi=%s) (price=%s)', $signal['divergence_1']['time'], $signal['divergence_1']['rsi'], $signal['divergence_1']['price']);
                $lines[] = sprintf('D2: [%s] (rsi=%s) (price=%s)', $signal['divergence_2']['time'], $signal['divergence_2']['rsi'], $signal['divergence_2']['price']);
                break;
        }
        return $this->lines($lines);
    }
}
