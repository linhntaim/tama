<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Actions\IAction;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingBroadcastProvider;
use Carbon\Carbon;

class BotBroadcaster
{
    public const IN_SECONDS = 120;

    protected TradingBroadcastProvider $provider;

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
        $this->provider = new TradingBroadcastProvider();
        $this->bot = BotFactory::create($trading->bot, $trading->options);
    }

    public function broadcast()
    {
        if (is_null($indication = $this->bot->indicate(1)->first())
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
        return $indication->get('now')
            && Carbon::now()->diffInSeconds($indication->get('time')) < self::IN_SECONDS;
    }

    protected function fineToCreateBroadcast(Indication $indication): bool
    {
        $tradingBroadcast = $this->provider
            ->notStrict()
            ->pinModel()
            ->first([
                'trading_id' => $this->trading->id,
                'time' => $indication->get('time'),
            ]);
        if (!is_null($tradingBroadcast) && !$tradingBroadcast->failed) {
            return false;
        }
        if (is_null($tradingBroadcast)) {
            $this->provider->createWithAttributes([
                'trading_id' => $this->trading->id,
                'time' => $indication->get('time'),
            ]);
        }
        else {
            $this->provider->doing();
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

    protected function updateBroadcast()
    {
        $this->provider->done();
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
