<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\Data\Indication;
use Illuminate\Support\Collection;

class CompositionOscilator extends Oscillator
{
    public const NAME = 'composition';

    protected function createComponents(): void
    {
        $this
            ->addComponent(new RsiComponent($this->options[RsiComponent::NAME] ?? []))
            ->addComponent(new StochRsiComponent($this->options[StochRsiComponent::NAME] ?? []));
    }

    /**
     * @param Packet $packet
     * @return Collection<int, Indication>
     */
    protected function getRsiIndications(Packet $packet): Collection
    {
        return $packet->get('transformers.rsi', collect([]))
            ->filter(function (Indication $indication) {
                return num_ne($indication->getValue(), 0.0);
            });
    }

    /**
     * @param Packet $packet
     * @return Collection<int, Indication>
     */
    protected function getStochRsiIndications(Packet $packet): Collection
    {
        return $packet->get('transformers.stoch_rsi', collect([]))
            ->filter(function (Indication $indication) {
                return num_ne($indication->getValue(), 0.0);
            });
    }

    protected function output(Packet $packet): Collection
    {
        $compositionIndications = [];
        $rsiIndications = $this->getRsiIndications($packet);
        $stochRsiIndications = $this->getStochRsiIndications($packet);
        for ($i = 0, $count = $this->getPrices($packet)->count(); $i < $count; ++$i) {
            if (!is_null($indication = $rsiIndications->get($i)) || !is_null($indication = $stochRsiIndications->get($i))) {
                $compositionIndications[$i] = $indication;
            }
        }
        return collect($compositionIndications);
    }
}