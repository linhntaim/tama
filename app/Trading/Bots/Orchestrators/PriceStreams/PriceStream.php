<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Support\Console\Commands\Command;
use App\Trading\Models\TradingProvider;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Ratchet\RFC6455\Messaging\Message;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use Throwable;

abstract class PriceStream
{
    public function __construct(
        protected string $exchange
    )
    {
    }

    protected function fetchTradings(): Collection
    {
        return (new TradingProvider())
            ->select(['ticker', 'interval'])
            ->group(['ticker', 'interval'])
            ->allByHavingSubscribers($this->exchange);
    }

    protected abstract function createSocketClient(LoopInterface $loop): mixed;

    protected abstract function listen($socketClient, Closure $onMessage);

    protected abstract function priceMessageExtractor(): IPriceMessageExtract;

    protected function pingBackTimer(): int|float
    {
        return 0;
    }

    protected function pingBack($socketClient)
    {
    }

    public function __invoke(LoopInterface $loop)
    {
        $socketClient = $this->createSocketClient($loop);
        $this->listen($socketClient, function ($conn, Message $message) {
            $this->proceedMessage($this->priceMessageExtractor(), $message);
        });
        if (($pingBackTimer = $this->pingBackTimer()) > 0) {
            $loop->addPeriodicTimer($pingBackTimer, fn() => $this->pingBack($socketClient));
        }
    }

    protected int $i = 0;

    protected function proceedMessage(IPriceMessageExtract $extract, Message $message)
    {
        $j = ++$this->i;
        $startTime = microtime(true);
        echo sprintf('[%s][%s] proceeding' . PHP_EOL, $j, date('Y-m-d H:i:s'));
        try {
            if (!is_null($latestPrice = $extract(json_decode_array($message->getPayload())))) {
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
