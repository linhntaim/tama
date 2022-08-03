<?php

namespace App\Trading\Bots;

class PlainTextBotReporter extends BotReporter
{
    protected function print(array $indication): string
    {
        return $this->lines([
            $this->title(),
            $this->subtitle(),
            $this->divide(),
            $this->indicationHeadline($indication),
            $this->indicationContent($indication),
        ]);
    }

    protected function lines(array $lines): string
    {
        return implode(PHP_EOL, $lines);
    }

    protected function divide(string $char = '-', int $times = 25): string
    {
        return str_repeat($char, $times);
    }

    protected function title(): string
    {
        return sprintf('BOT: %s', $this->bot->getDisplayName());
    }

    protected function subtitle(): string
    {
        return sprintf('Exchange: %s - Ticker: %s - Interval: %s',
            $this->bot->exchange(),
            $this->bot->ticker(),
            $this->bot->interval(),
        );
    }

    protected function indicationHeadline(array $indication): string
    {
        return sprintf('Indication(s): %s', count($indication));
    }

    protected function indicationContent(array $indication): string
    {
        $lines = [];
        foreach ($indication as $item) {
            $lines[] = $this->divide('=');
            $lines[] = sprintf('[%s]', $item['time']);
            $lines[] = sprintf('%s @ %s', $item['value'] == -1 ? 'BUY' : 'SELL', $item['price']);
            $lines[] = $this->indicationMeta($item['meta']);
        }
        return $this->lines($lines);
    }

    protected function indicationMeta(array $meta): string
    {
        return '[meta]';
    }
}
