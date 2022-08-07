<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Data\IndicationMetaItem;
use App\Trading\Bots\Data\Signal;
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

    protected function headlineMetaItem(IndicationMetaItem $metaItem): string
    {
        if ($metaItem->getType() == 'rsi') {
            return sprintf('{RSI=%s}', $metaItem->get('rsi'));
        }
        return parent::headlineMetaItem($metaItem);
    }

    protected function contentSignal(Signal $signal): string
    {
        $lines = [];
        switch ($signal->getType()) {
            case 'bullish_divergence':
            case 'bearish_divergence':
                $lines[] = sprintf(
                    'D1: [%s] (rsi=%s) (price=%s)',
                    $signal->get('divergence_1.time'),
                    $signal->get('divergence_1.rsi'),
                    $signal->get('divergence_1.price')
                );
                $lines[] = sprintf(
                    'D2: [%s] (rsi=%s) (price=%s)',
                    $signal->get('divergence_2.time'),
                    $signal->get('divergence_2.rsi'),
                    $signal->get('divergence_2.price')
                );
                break;
        }
        return $this->lines($lines);
    }
}
