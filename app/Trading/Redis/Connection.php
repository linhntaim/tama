<?php

namespace App\Trading\Redis;

use App\Trading\Redis\Resp\RespData;
use App\Trading\Redis\Resp\Factory as RespFactory;
use App\Trading\Redis\Resp\RespError;
use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;

abstract class Connection implements ConnectionInterface
{
    protected RespFactory $respFactory;

    protected string $prefix = '';

    public function __construct(
        protected ConnectionInterface $connection
    )
    {
        $this->respFactory = new RespFactory;
        $this->connection->on('data', function (string $data) {
            if (!is_null($respData = $this->respFactory->create($data))) {
                $this->emitRespData($respData);
            }
        });
    }

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;
        return $this;
    }

    protected function emitRespData(RespData $respData)
    {
        if ($respData instanceof RespError) {
            $this->emit('error', [$respData]);
        }
    }

    public function getRemoteAddress(): ?string
    {
        return $this->connection->getRemoteAddress();
    }

    public function getLocalAddress(): ?string
    {
        return $this->connection->getLocalAddress();
    }

    public function on($event, callable $listener): static
    {
        $this->connection->on($event, $listener);
        return $this;
    }

    public function once($event, callable $listener): static
    {
        $this->connection->once($event, $listener);
        return $this;
    }

    public function removeListener($event, callable $listener): static
    {
        $this->connection->removeListener($event, $listener);
    }

    public function removeAllListeners($event = null): static
    {
        $this->connection->removeAllListeners($event);
    }

    public function listeners($event = null)
    {
        return $this->connection->listeners($event);
    }

    public function emit($event, array $arguments = [])
    {
        $this->connection->emit($event, $arguments);
    }

    public function isReadable(): bool
    {
        return $this->connection->isReadable();
    }

    public function pause()
    {
        $this->connection->pause();
    }

    public function resume()
    {
        $this->connection->resume();
    }

    public function pipe(WritableStreamInterface $dest, array $options = [])
    {
        return $this->connection->pipe($dest, $options);
    }

    public function close()
    {
        $this->connection->close();
    }

    public function isWritable(): bool
    {
        return $this->connection->isWritable();
    }

    public function write($data): bool
    {
        return $this->connection->write($data);
    }

    public function end($data = null)
    {
        $this->connection->end();
    }

    public function select(string $index = '0'): static
    {
        return $this->command('select', (string)$index);
    }

    public function command(string $command, string|int ...$arguments): static
    {
        $this->connection->write($this->composeRespArray(func_get_args()));
        return $this;
    }

    protected function composeRespArray(array $arguments): string
    {
        $data = [sprintf('*%d', count($arguments))];
        foreach ($arguments as $argument) {
            if (is_string($argument)) {
                array_push($data, sprintf('$%d', strlen($argument)), $argument);
            }
            elseif (is_int($argument)) {
                array_push($data, sprintf(':%d', $argument));
            }
        }
        $data[] = '';
        return implode(RespData::CRLF, $data);
    }
}
