<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\BotSlug;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Exchanges\PriceCollection;
use Illuminate\Support\Collection;
use RuntimeException;

abstract class Oscillator
{
    use BotSlug;

    public const NAME = '';

    /**
     * @var array<string, Component>|Component[]
     */
    protected array $components = [];

    public function __construct(
        protected array $options = []
    )
    {
        $this->createComponents();
    }

    final public function getName(): string
    {
        return static::NAME;
    }

    public function options(): array
    {
        if (count($this->components) === 1) {
            return array_values($this->components)[0]->options();
        }
        return array_map(static function (Component $component) {
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

    protected function optionsAsSlug(): string
    {
        if (count($this->components) === 1) {
            return $this->slugConcat(...$this->options());
        }
        return $this->slugConcat(...array_map(static function (Component $component) {
            return $component->asSlug();
        }, $this->components));
    }

    public function asSlug(): string
    {
        return $this->slugConcat(
            $this->getName(),
            $this->optionsAsSlug(),
        );
    }

    protected function createComponents(): void
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
     * @param PriceCollection $prices
     * @param bool|int $latest
     * @return Collection<int, Indication>
     */
    public function run(PriceCollection $prices, bool|int $latest = true): Collection
    {
        return $this->output(
            $this->process(
                $this->input([
                    'prices' => $prices,
                ]),
                $latest
            )
        );
    }

    protected function input(array $inputs): Packet
    {
        return tap(new Packet(), static function (Packet $packet) use ($inputs) {
            foreach ($inputs as $name => $value) {
                $packet->set('inputs.' . $name, $value);
            }
        });
    }

    protected function process(Packet $packet, bool|int $latest = true): Packet
    {
        foreach ($this->components as $component) {
            $component->transmit($packet, $latest);
        }
        return $packet;
    }

    /**
     * @param Packet $packet
     * @return Collection<int, Indication>
     */
    abstract protected function output(Packet $packet): Collection;
}
