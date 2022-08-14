<?php

namespace App\Trading\Redis;

use App\Trading\Redis\Resp\RespArray;
use App\Trading\Redis\Resp\RespData;

class PubSubConnection extends Connection
{
    protected function emitRespData(RespData $respData)
    {
        parent::emitRespData($respData);

        if ($respData instanceof RespArray) {
            $output = $respData->output();
            if (count($output) >= 3 && $output[0] == 'message') {
                $this->emit(sprintf('channel:%s', $output[1]), [$output[2]]);
                $this->emit('channel', [$output[1], $output[2]]);
            }
        }
    }

    public function onChannel(string $channel, callable $listener): static
    {
        return $this->on(sprintf('channel:%s', $this->prefix . $channel), $listener);
    }

    public function onChannels(callable $listener): static
    {
        return $this->on('channel', $listener);
    }

    public function publish(string $channel, string $message): static
    {
        return $this->command('publish', $this->prefix . $channel, $message);
    }

    public function subscribe(string $channel, ?callable $listener = null): static
    {
        $this->command('subscribe', $this->prefix . $channel);
        return is_null($listener) ? $this : $this->onChannel($channel, $listener);
    }
}
