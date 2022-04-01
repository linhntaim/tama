<?php

/**
 * Base
 */

namespace App\Support\Http;

class BagString
{
    public static function create($bag): ?static
    {
        if (!is_countable($bag) || !is_iterable($bag)) {
            return null;
        }
        if (count($bag) <= 0) {
            return null;
        }
        return new static($bag);
    }

    protected iterable $bag;

    protected int $nameLength = 1;

    private function __construct($bag)
    {
        $this->bag = $bag;
        $this->resolveNameLength();
    }

    public function getNameLength(): int
    {
        return $this->nameLength;
    }

    public function setNameLength(int $nameLength): static
    {
        $this->nameLength = $nameLength;
        return $this;
    }

    public function resolveNameLength(): static
    {
        foreach ($this->bag as $name => $_) {
            if (($length = strlen($name)) > $this->nameLength - 1) {
                $this->nameLength = $length + 1;
            }
        }
        return $this;
    }

    protected function stringifyItem(string $name, $item): string|array
    {
        return sprintf('%s %s', $this->stringifyName($name), is_scalar($item) ? $item : json_encode($item));
    }

    protected function stringifyName(string $name): string
    {
        return sprintf("%-{$this->nameLength}s", $name . ':');
    }

    protected function stringifyItems(array $stringifiedItems): string
    {
        return implode(PHP_EOL, $stringifiedItems);
    }

    protected function pushToStringifiedItems(array &$stringifiedItems, string $name, $item)
    {
        $stringifiedItem = $this->stringifyItem($name, $item);
        if (is_array($stringifiedItem)) {
            array_push($stringifiedItems, ...$stringifiedItem);
        }
        else {
            array_push($stringifiedItems, $stringifiedItem);
        }
    }

    public function __toString(): string
    {
        $stringifiedItems = [];
        foreach ($this->bag as $name => $item) {
            $this->pushToStringifiedItems($stringifiedItems, $name, $item);
        }
        return $this->stringifyItems($stringifiedItems);
    }
}