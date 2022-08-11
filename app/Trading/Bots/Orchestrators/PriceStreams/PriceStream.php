<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Support\Console\Commands\Command;
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

    public abstract function __invoke(LoopInterface $loop);

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
                        'php "%s" orchestration:price "%s" "%s" "%s" "%s" %s',
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
