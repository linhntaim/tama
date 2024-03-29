<?php

namespace App\Trading\Redis\Resp;

class RespArray extends RespData
{
    /**
     * @return array<int, string|int|null>
     */
    public function output(): array
    {
        return array_map(static fn(?RespData $respData) => $respData?->output(), $this->data);
    }
}
