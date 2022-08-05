<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\Indication;
use App\Trading\Prices\Prices;
use Illuminate\Support\Collection;
use RuntimeException;

abstract class Oscillator
{
    public const NAME = '';

    /**
     * @var array<string, Component>
     */
    protected array $components = [];

    public function __construct(
        protected array $options = []
    )
    {
        $this->createComponents();
    }

    public final function getName(): string
    {
        return static::NAME;
    }

    public function options(): array
    {
        if (count($this->components) == 1) {
            return array_values($this->components)[0]->options();
        }
        return array_map(function (Component $component) {
            return $component->options();
        }, $this->components);
    }

    public function asOptions(): array
    {
        return [
            'name' => $this->getName(),
            'options' => $this->options(),
        ];
    }

    public function asSlug(): string
    {
        if (count($this->components) == 1) {
            return implode('-', [
                $this->getName(),
                ...$this->options(),
            ]);
        }
        return implode('-', [
            $this->getName(),
            ...array_map(function (Component $component) {
                return $component->asSlug();
            }, $this->components),
        ]);
    }

    protected function createComponents()
    {
    }

    protected function addComponent(Component $component): static
    {
        $this->components[$component->getName()] = $component;
        return $this;
    }

    protected function component(string $name): Component
    {
        return $this->components[$name] ?? throw new RuntimeException('Component does not exist.');
    }

    /**
     * @param Prices $prices
     * @return Collection<int, Indication>
     */
    public function run(Prices $prices): Collection
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

    /**
     * @param Packet $packet
     * @return Collection<int, Indication>
     */
    protected abstract function output(Packet $packet): Collection;
}
