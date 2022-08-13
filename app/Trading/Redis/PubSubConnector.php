<?php

namespace App\Trading\Redis;

use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Socket\ConnectorInterface;

class PubSubConnector implements ConnectorInterface
{
    protected LoopInterface $loop;

    public function __construct(?LoopInterface $loop = null)
    {
        $this->loop = $loop ?: Loop::get();
    }

    public function connect($uri): PromiseInterface
    {
        return (new Connector([], $this->loop))
            ->connect($uri)
            ->then(function (ConnectionInterface $connection) {
                return new PubSubConnection($connection);
            });
    }
}
