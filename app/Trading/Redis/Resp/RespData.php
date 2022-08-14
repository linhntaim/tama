<?php

namespace App\Trading\Redis\Resp;

class RespData
{
    public const CRLF = "\r\n";

    protected mixed $data;

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    public function output(): mixed
    {
        return $this->data;
    }
}
