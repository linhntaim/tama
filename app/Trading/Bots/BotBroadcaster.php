<?php

namespace App\Trading\Bots;

use App\Support\Client\DateTimer;
use App\Trading\Bots\Actions\IAction;
use App\Trading\Bots\Data\Indication;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingBroadcastProvider;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

class BotBroadcaster
{
    public const IN_SECONDS = 120;

    protected TradingBroadcastProvider $tradingBroadcastProvider;

    protected Bot $bot;

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
        $this->bot = BotFactory::create($trading->bot, array_merge($trading->options, [
            'safe_ticker' => true,
            'safe_interval' => true,
        ]));
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function broadcast(): void
    {
        if (is_null($indication = $this->bot->indicateNow())
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
        return $indication->getActionNow()
            && DateTimer::now()->diffInSeconds(DateTimer::timeAs($indication->getActionTime())) < self::IN_SECONDS;
    }

    protected function fineToCreateBroadcast(Indication $indication): bool
    {
        $actionTime = DateTimer::timeAsDatabase($indication->getActionTime());
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
        $this->trading->subscribers->load('socials');
        foreach ($this->actions as $action) {
            $action($this->trading, $this->bot, $indication);
        }
        return $this;
    }
}
