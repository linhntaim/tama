<?php

namespace App\Support\Trading\Strategies\Data;

abstract class Data
{
    /**
     * @var array|DataItem[]
     */
    protected array $data;

    /**
     * @param array|DataItem[] $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getValues(): array
    {
        return array_map(function (DataItem $item) {
            return $item->getValue();
        }, $this->data);
    }
}
