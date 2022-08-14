<?php

namespace App\Trading\Bots\Reporters;

use App\Support\Client\DateTimer;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Data\IndicationMetaItem;
use App\Trading\Bots\Data\Signal;
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
        $lines[] = sprintf(
            '[%s] %s',
            DateTimer::timeAs($indication->getActionTime(), 'Y-m-d H:i:s'),
            $indication->getActionNow() ? '<!>' : ''
        );
        $lines[] = sprintf('%s @ %s', $indication->getAction(), $indication->getPrice());
        $lines[] = sprintf(
            '{Time=%s}',
            DateTimer::timeAs($indication->getTime(), 'Y-m-d H:i:s')
        );
        foreach ($indication->getMeta() as $metaItem) {
            $lines[] = $this->headlineMetaItem($metaItem);
            $lines[] = $this->contentMetaItem($metaItem);
        }

        return $this->lines($lines);
    }

    protected function headlineMetaItem(IndicationMetaItem $metaItem): string
    {
        return sprintf('{%s}', strtoupper($metaItem->getType()));
    }

    protected function contentMetaItem(IndicationMetaItem $metaItem): string
    {
        $lines = [];
        foreach ($metaItem->getSignals() as $signal) {
            $lines[] = $this->headlineSignal($signal);
            $lines[] = $this->contentSignal($signal);
        }
        return $this->lines($lines);
    }

    protected function headlineSignal(Signal $signal): string
    {
        return sprintf('#%s:%s', $signal->getType(), $signal->getStrength());
    }

    protected function contentSignal(Signal $signal): string
    {
        return '-';
    }
}
