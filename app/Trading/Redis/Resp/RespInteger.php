<?php

namespace App\Trading\Redis\Resp;

/**
 * @method int output()
 */
class RespInteger extends RespData
{
    public function __construct(mixed $data)
    {
        parent::__construct((int)$data);
    }
}
