<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\BotSlug;
use App\Trading\Bots\Data\Analysis;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Exchanges\PriceCollection;
use Illuminate\Support\Collection;

abstract class Component
{
    use BotSlug;

    public const NAME = '';

    /**
     * @param array $options Must be a 1-dimension array
     */
    public function __construct(
        protected array $options = []
    )
    {
    }

    final public function getName(): string
    {
        return static::NAME;
    }

    public function options(): array
    {
        return $this->options;
    }

    public function asOptions(): array
    {
        return [
            'name' => $this->getName(),
            'options' => $this->options(),
        ];
    }

    protected function optionsAsSlug(): string
    {
        return $this->slugConcat(...$this->options());
    }

    public function asSlug(): string
    {
        return $this->slugConcat(
            $this->getName(),
            $this->optionsAsSlug(),
        );
    }

    public function transmit(Packet $packet, bool|int $latest = true): Packet
    {
        return $this->transform(
            $this->analyze(
                $this->convert($packet),
                $latest
            )
        );
    }

    protected function getPrices(Packet $packet): PriceCollection
    {
        return $packet->get('inputs.prices');
    }

    protected function convert(Packet $packet): Packet
    {
        return $packet->set('converters.' . static::NAME, $this->converted($packet));
    }

    abstract protected function converted(Packet $packet): mixed;

    protected function analyze(Packet $packet, bool|int $latest = true): Packet
    {
        return $packet->set('analyzers.' . static::NAME, $this->analyzed($packet, $latest));
    }

    /**
     * @param Packet $packet
     * @param bool|int $latest
     * @return Collection<int, Analysis>
     */
    abstract protected function analyzed(Packet $packet, bool|int $latest = true): Collection;

    protected function transform(Packet $packet): Packet
    {
        return $packet->set('transformers.' . static::NAME, $this->transformed($packet));
    }

    /**
     * @param Packet $packet
     * @return Collection<int, Indication>
     */
    protected function transformed(Packet $packet): Collection
    {
        return $packet->get('analyzers.' . static::NAME)
            ->map(function (Analysis $analysis) {
                return new Indication(
                    $this->transformedIndicationValue($analysis),
                    $analysis->getTime(),
                    $analysis->getPrice(),
                    $this->transformedIndicationMeta($analysis)
                );
            });
    }

    protected function transformedIndicationValue(Analysis $analysis): float
    {
        return 0.0;
    }

    protected function transformedIndicationMeta(Analysis $analysis): array
    {
        return [];
    }
}
