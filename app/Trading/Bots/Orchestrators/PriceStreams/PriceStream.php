<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Support\Console\Commands\Command;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Ratchet\Client\Connector as ClientSocketConnector;
use Ratchet\Client\WebSocket as ClientSocketConnection;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\Message;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Socket\Connector as SocketConnector;
use Throwable;

abstract class PriceStream
{
    protected ClientSocketConnection $connection;

    protected IPriceMessageExtract $messageExtract;

    protected int $reconnectInterval = 60;

    private int $id = 0;

    public function __construct(
        protected LoopInterface $loop,
        protected string        $exchange,
        protected string        $endpoint,
        protected int           $pingInterval = 3 * 60
    )
    {
        $this->messageExtract = $this->createMessageExtractor();
    }

    protected abstract function createMessageExtractor(): IPriceMessageExtract;

    protected function getId(): int
    {
        return ++$this->id;
    }

    protected function fetchTradings(): Collection
    {
        return (new TradingProvider())
            ->select(['ticker', 'interval'])
            ->group(['ticker', 'interval'])
            ->allByHavingSubscribers($this->exchange);
    }

    protected function setConnection(ClientSocketConnection $connection): static
    {
        $this->connection = $connection;
        return $this;
    }

    protected function getConnection(): ?ClientSocketConnection
    {
        return $this->connection ?? null;
    }

    protected function log(string $message)
    {
        Log::info(sprintf('Price stream "%s": %s.', $this->exchange, $message));
    }

    protected function connect()
    {
        (new ClientSocketConnector($this->loop, new SocketConnector($this->loop)))($this->endpoint)
            ->then(
                fn(ClientSocketConnection $connection) => $this->setConnection($connection)->onConnected(),
                fn(Throwable $e) => $this->onFailed($e)
            );
    }

    protected function reconnect()
    {
        $this->log('Reconnecting');
        $this->loop->addTimer($this->reconnectInterval, fn() => $this->connect());
    }

    protected function onFailed(Throwable $e)
    {
        report($e);
    }

    protected function onConnected()
    {
        $this->log('Connected');
        $this->listen();
        $this->subscribe();
        $this->setPingInterval();
    }

    protected function listen()
    {
        $this->getConnection()
            ->on('message', fn(Message $message) => $this->onMessage($message))
            ->on('ping', fn() => $this->onPing())
            ->on('pong', fn() => $this->onPong())
            ->on('close', fn() => $this->onClose());
    }

    protected function onMessage(Message $message)
    {
        $this->proceedMessage($message);
    }

    protected function onPing()
    {
        $this->log('Ping received');
        $this->pong();
    }

    protected function onPong()
    {
        $this->log('Pong received');
    }

    protected function onClose()
    {
        $this->log('Closed');
        $this->reconnect();
    }

    protected function subscribe()
    {
        $this->subscribeTradings($this->fetchTradings());
    }

    /**
     * @param Collection<int, Trading> $tradings
     * @return void
     */
    protected abstract function subscribeTradings(Collection $tradings);

    public abstract function subscribeTrading(string $ticker, string $interval);

    public abstract function unsubscribeTrading(string $ticker, string $interval);

    protected function setPingInterval()
    {
        if ($this->pingInterval > 0) {
            $this->loop->addPeriodicTimer(
                $this->pingInterval,
                fn() => $this->ping()
            );
        }
    }

    protected function ping()
    {
        $this->getConnection()->send(new Frame('', true, Frame::OP_PING));
    }

    protected function pong()
    {
        $this->getConnection()->send(new Frame('', true, Frame::OP_PONG));
    }

    protected function wait()
    {
        Redis::connection()->subscribe();
    }

    public function __invoke(): static
    {
        $this->log('Connecting');
        $this->connect();
        return $this;
    }

    protected int $i = 0;

    protected function proceedMessage(Message $message)
    {
        $j = ++$this->i;
        $startTime = microtime(true);
        echo sprintf('[%s][%s] proceeding' . PHP_EOL, $j, date('Y-m-d H:i:s'));
        try {
            if (!is_null($latestPrice = ($this->messageExtract)(json_decode_array($message->getPayload())))) {
                (new Process(
                    sprintf(
                        'php "%s" orchestration:latest-price "%s" "%s" "%s" "%s" %s',
                        base_path('artisan'),
                        $latestPrice->getExchange(),
                        $latestPrice->getTicker(),
                        $latestPrice->getInterval(),
                        base64_encode(json_encode($latestPrice->getPrice())),
                        Command::PARAMETER_OFF_SHOUT_OUT
                    ),
                    null,
                    null,
                    []
                ))->start();
            }
        }
        catch (Throwable $throwable) {
            report($throwable);
        }
        echo sprintf('[%s][%s] proceeded in %sms' . PHP_EOL, $j, date('Y-m-d H:i:s'), number_format((microtime(true) - $startTime) * 1000, 2));
    }
}
