<?php

namespace App\Support\Trading\Strategies\Executors;

use App\Support\Trading\Strategies\Model\Strategy;
use App\Support\Trading\Strategies\Signals\SignalFactory;

class CustomExecutor extends Executor
{
    public const NAME = 'custom';

    public function __construct(Strategy $strategy)
    {
        parent::__construct($strategy);

        foreach ($strategy->signals as $signal) {
            switch (true) {
                case $signal->isBullish:
                    $this->addBullishSignal(SignalFactory::createBullishSignal($signal->name), $signal->score);
                    break;
                case $signal->isBearish:
                    $this->addBearishSignal(SignalFactory::createBearishSignal($signal->name), $signal->score);
                    break;
            }
        }
    }
}
