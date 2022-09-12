<?php

namespace App\Trading\Bots\Exchanges;

use App\Support\Client\DateTimer;
use App\Support\Console\Commands\Command;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use Illuminate\Database\Eloquent\Collection;
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
    private const SUBSCRIPTION_STATUS_NOT_STARTED = 0;
    private const SUBSCRIPTION_STATUS_STARTED = 1;
    private const SUBSCRIPTION_STATUS_ENDED = 2;

    private int $id = 0;

    protected PriceMessageExtract $messageExtract;

    protected ?ClientSocketConnection $connection = null;

    private ?Collection $subscriptionTradingChunks = null;

    private ?string $subscriptionCurrentKey = null;

    private int $subscriptionStatus = self::SUBSCRIPTION_STATUS_NOT_STARTED;

    public function __construct(
        protected LoopInterface $loop,
        protected string        $exchange,
        protected string        $endpoint,
        protected int           $pingInterval = 3 * 60
    )
    {
        $this->messageExtract = $this->createMessageExtractor();
    }

    abstract protected function createMessageExtractor(): PriceMessageExtract;

    final protected function getId(): int
    {
        return ++$this->id;
    }

    protected function fetchTradings(): Collection
    {
        return (new TradingProvider())
            ->select(['ticker', 'interval'])
            ->group(['ticker', 'interval'])
            ->allByRunning($this->exchange);
    }

    private function setConnection(ClientSocketConnection $connection): static
    {
        $this->connection = $connection;
        return $this;
    }

    protected function getConnection(): ?ClientSocketConnection
    {
        return $this->connection;
    }

    protected function log(string $message): void
    {
        echo sprintf('[%s] Price stream "%s": %s.' . PHP_EOL, date('Y-m-d H:i:s'), $this->exchange, $message);
    }

    protected function nowQuarterOfHourUntil(): int
    {
        $now = DateTimer::now(null);
        return
            (clone $now)->minute(0)->second(0)
                ->addMinutes(15 * int_floor($now->minute / 15 + 1))
                ->getTimestamp()
            - $now->getTimestamp();
    }

    protected function connect(): void
    {
        // should be connect at every quarter of an hour plus 1 sec
        $this->loop->addTimer($this->nowQuarterOfHourUntil() + 1, function () {
            $this->log('Ding-a-dong');
            (new ClientSocketConnector($this->loop, new SocketConnector($this->loop)))($this->endpoint)
                ->then(
                    fn(ClientSocketConnection $connection) => $this->setConnection($connection)->onConnected(),
                    fn(Throwable $e) => $this->onFailed($e)
                );
        });
    }

    protected function reconnect(): void
    {
        $this->log('Reconnecting');

        $this->endSubscribing();
        $this->id = 0;
        $this->connection = null;
        $this->subscriptionStatus = self::SUBSCRIPTION_STATUS_NOT_STARTED;

        $this->connect();
    }

    protected function onFailed(Throwable $e): void
    {
        $this->log(sprintf('ERR Failed to connect: %s', $e->getMessage()));
        report($e);
    }

    protected function onConnected(): void
    {
        $this->log('Connected');
        $this->listen();
        $this->subscribe();
        $this->setPingInterval();
    }

    protected function listen(): void
    {
        $this->getConnection()
            ?->on('message', fn(Message $message) => $this->onMessage($message))
            ->on('ping', fn() => $this->onPing())
            ->on('pong', fn() => $this->onPong())
            ->on('close', fn(?int $code, ?string $reason) => $this->onClose($code, $reason))
            ->on('error', fn(Throwable $e) => $this->onError($e));
    }

    protected function onMessage(Message $message): void
    {
        switch ($this->subscriptionStatus) {
            case self::SUBSCRIPTION_STATUS_ENDED:
                $this->proceedMessage($message);
                break;
            case self::SUBSCRIPTION_STATUS_STARTED:
                $this->proceedMessageWhileSubscribing($message);
                break;
            default:
                $this->log(sprintf('Message: %s', $message->getPayload()));
                break;
        }
    }

    protected function onPing(): void
    {
        $this->log('Ping received');
        $this->pong();
    }

    protected function onPong(): void
    {
        $this->log('Pong received');
    }

    protected function onClose(?int $code, ?string $reason): void
    {
        $this->log(sprintf('Closed (%s - %s)', $code ?: 0, $reason ?: 'Unknown'));
        $this->reconnect();
    }

    protected function onError(Throwable $e): void
    {
        $this->log(sprintf('ERR Caught: %s', $e->getMessage()));
        report($e);
    }

    protected function endSubscribing(): void
    {
        $this->subscriptionTradingChunks = null;
        $this->subscriptionCurrentKey = null;
    }

    protected function subscribe(): void
    {
        $this->subscriptionTradingChunks = $this->fetchTradings()->chunk(50);
        $this->subscriptionStatus = self::SUBSCRIPTION_STATUS_STARTED;

        $this->log('Start subscribing');
        $this->subscribeTradingChunks();
    }

    protected function subscribeTradingChunks(): void
    {
        $tradings = $this->subscriptionTradingChunks->shift();
        if (!is_null($tradings) && $tradings->count()) {
            (fn(Trading $trading) => $this->subscriptionCurrentKey = $this->createSubscriptionKey($trading->ticker, $trading->interval))($tradings->first());
            $this->log(sprintf('Chunk subscribing from: %s - %s item(s)', $this->subscriptionCurrentKey, $tradings->count()));
            $this->subscribeTradings($tradings);
        }
        else {
            $this->endSubscribing();
            $this->subscriptionStatus = self::SUBSCRIPTION_STATUS_ENDED;
            $this->log('End subscribing');
        }
    }

    protected function createSubscriptionKey(string $ticker, string $interval): string
    {
        return sprintf('%s_%s', $ticker, $interval);
    }

    /**
     * @param Collection<int, Trading> $tradings
     * @return void
     */
    abstract protected function subscribeTradings(Collection $tradings): void;

    abstract public function subscribeTrading(string $ticker, string $interval): void;

    abstract public function unsubscribeTrading(string $ticker, string $interval): void;

    protected function setPingInterval(): void
    {
        if ($this->pingInterval > 0) {
            $this->loop->addPeriodicTimer(
                $this->pingInterval,
                fn() => $this->ping()
            );
        }
    }

    protected function send(string|array $payload, int $opcode = Frame::OP_TEXT, bool $final = true): void
    {
        $this->getConnection()?->send(
            new Frame(is_array($payload) ? json_encode_readable($payload) : $payload, $final, $opcode)
        );
    }

    protected function ping(): void
    {
        $this->send('', Frame::OP_PING);
        $this->log('Ping sent');
    }

    protected function pong(): void
    {
        $this->send('', Frame::OP_PONG);
        $this->log('Pong sent');
    }

    public function __invoke(): static
    {
        $this->log('Connecting');
        $this->connect();
        return $this;
    }

    protected function transformMessage(Message $message): array
    {
        return json_decode_array($message->getPayload()) ?: [];
    }

    protected function proceedMessageWhileSubscribing(Message $message): void
    {
        $ticker = $interval = null;
        ($this->messageExtract)($this->transformMessage($message), $ticker, $interval);
        if (!is_null($ticker) && !is_null($interval)
            && $this->subscriptionCurrentKey === ($key = $this->createSubscriptionKey($ticker, $interval))) {
            $this->subscriptionCurrentKey = null;
            $this->log(sprintf('Chunk subscribed from:  %s', $key));
            $this->subscribeTradingChunks(); // keep subscribing
        }
    }

    protected function proceedMessage(Message $message): void
    {
        try {
            if (!is_null($latestPrice = ($this->messageExtract)($this->transformMessage($message)))) {
                (new Process(
                    sprintf(
                        'php "%s" orchestration:latest-price "%s" "%s" "%s" "%s" %s',
                        base_path('artisan'),
                        $latestPrice->getExchange(),
                        $latestPrice->getTicker(),
                        $latestPrice->getInterval(),
                        base64_encode(json_encode_readable($latestPrice->getPrice()->toArray())),
                        Command::PARAMETER_OFF_SHOUT_OUT
                    ),
                    null,
                    null,
                    []
                ))->start();
            }
        }
        catch (Throwable $e) {
            $this->log(sprintf('ERR Caught: %s', $e->getMessage()));
            report($e);
        }
    }
}
