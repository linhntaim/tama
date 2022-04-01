<?php

/**
 * Base
 */

namespace App\Support\Http;

class HeaderBagString extends BagString
{
    /**
     * @param string $name
     * @param array $item
     * @return string|array
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