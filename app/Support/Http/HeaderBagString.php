<?php

namespace App\Support\Http;

use JsonException;

class HeaderBagString extends BagString
{
    /**
     * @param string $name
     * @param array $item
     * @return string|array
     * @throws JsonException
     */
    protected function stringifyItem(string $name, $item): string|array
    {
        $stringified = [];
        foreach ($item as $i) {
            $stringified[] = parent::stringifyItem($name, $i);
        }
        return implode(PHP_EOL, $stringified);
    }
}
