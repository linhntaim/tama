<?php

namespace App\Trading\Bots;

use App\Support\Client\DateTimer;
use App\Trading\Bots\Actions\IAction;
use App\Trading\Bots\Data\Indication;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingBroadcastProvider;

class BotBroadcaster
{
    public const IN_SECONDS = 120;

    protected TradingBroadcastProvider $tradingBroadcastProvider;

    protected Bot $bot;

    protected bool $actionNow;

    protected int $actionTime;

    /**
     * @param Trading $trading
     * @param IAction[] $actions
     */
    public function __construct(
        protected Trading $trading,
        protected array   $actions
    )
    {
        $this->tradingBroadcastProvider = new TradingBroadcastProvider();
        $this->bot = BotFactory::create($trading->bot, $trading->botOptions);
    }

    public function broadcast(): void
    {
        if (is_null($indication = take($this->bot->indicateNow(), function (?Indication $indication) {
                if (!is_null($indication)) {
                    [$this->actionNow, $this->actionTime] = [
                        $indication->getActionNow($this->bot->interval()),
                        $indication->getActionTime($this->bot->interval()),
                    ];
                }
            }))
            || !$this->canBroadcast($indication)) {
            return;
        }
        $this
            ->beforeBroadcasting($indication)
            ->broadcasting($indication)
            ->afterBroadcasting($indication);
    }

    protected function canBroadcast(Indication $indication): bool
    {
        return $this->fineTime($indication)
            && $this->fineToCreateBroadcast($indication);
    }

    protected function fineTime(Indication $indication): bool
    {
        return $this->actionNow
            && DateTimer::now(null)->diffInSeconds(DateTimer::timeAs($this->actionTime)) < self::IN_SECONDS;
    }

    protected function fineToCreateBroadcast(Indication $indication): bool
    {
        $actionTime = DateTimer::timeAsDatabase($this->actionTime);
        $tradingBroadcast = $this->tradingBroadcastProvider
            ->notStrict()
            ->pinModel()
            ->first([
                'trading_id' => $this->trading->id,
                'time' => $actionTime,
            ]);
        if (!is_null($tradingBroadcast) && !$tradingBroadcast->failed) {
            return false;
        }
        if (is_null($tradingBroadcast)) {
            $this->tradingBroadcastProvider->createWithAttributes([
                'trading_id' => $this->trading->id,
                'time' => $actionTime,
                'indication' => $indication,
            ]);
        }
        else {
            $this->tradingBroadcastProvider->doing();
        }
        return true;
    }

    protected function beforeBroadcasting(Indication $indication): static
    {
        return $this;
    }

    protected function afterBroadcasting(Indication $indication): static
    {
        $this->updateBroadcast();
        return $this;
    }

    protected function updateBroadcast(): void
    {
        $this->tradingBroadcastProvider->done();
    }

    protected function broadcasting(Indication $indication): static
    {
        foreach ($this->actions as $action) {
            $action($this->trading, $this->bot, $this->tradingBroadcastProvider->model());
        }
        return $this;
    }
}
