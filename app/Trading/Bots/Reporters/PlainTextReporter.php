<?php

namespace App\Trading\Bots\Reporters;

use App\Trading\Bots\Indication;
use Illuminate\Support\Collection;

class PlainTextReporter extends Reporter
{
    /**
     * @param Collection<int, Indication> $indications
     * @return string
     */
    protected function print(Collection $indications): string
    {
        return $this->lines(array_filter([
            $this->title(),
            $this->subtitle(),
            $this->headlineIndications($indications),
            $this->contentIndications($indications),
        ]));
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

    /**
     * @param Collection<int, Indication> $indications
     * @return string|null
     */
    protected function headlineIndications(Collection $indications): ?string
    {
        if (($count = count($indications)) == 1) {
            return null;
        }
        return $this->lines([
            $this->divide(),
            sprintf('Indication(s): %s', $count),
        ]);
    }

    /**
     * @param Collection<int, Indication> $indications
     * @return string
     */
    protected function contentIndications(Collection $indications): string
    {
        return $this->lines(
            $indications
                ->map(fn(Indication $indication) => $this->contentIndication($indication))
                ->all()
        );
    }

    protected function contentIndication(Indication $indication): string
    {
        $lines = [];
        $lines[] = $this->divide('=');
        $lines[] = sprintf('[%s]', $indication->get('time'));
        $lines[] = sprintf('%s @ %s', $indication->get('value') == -1 ? 'BUY' : 'SELL', $indication->get('price'));
        $lines[] = $this->indicationMeta($indication->get('meta'));
        return $this->lines($lines);
    }

    protected function indicationMeta(array $meta): string
    {
        return '[meta]';
    }
}
