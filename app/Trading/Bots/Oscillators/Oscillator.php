<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Prices\Prices;

abstract class Oscillator
{
    public function __construct(
        protected array $options = []
    )
    {
    }

    public function run(Prices $prices): array
    {
        return $this->output(
            $this->process(
                $this->input([
                    'prices' => $prices,
                ])
            )
        );
    }

    protected function input(array $inputs): Packet
    {
        return take(new Packet(), function (Packet $packet) use ($inputs) {
            foreach ($inputs as $name => $value) {
                $packet->set('inputs.' . $name, $value);
            }
        });
    }

    protected abstract function process(Packet $packet): Packet;

    protected abstract function output(Packet $packet): array;
}
