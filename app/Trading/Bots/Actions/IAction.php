<?php

namespace App\Trading\Bots\Actions;

use App\Trading\Bots\Bot;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingBroadcast;

interface IAction
{
    public function __invoke(Trading $trading, Bot $bot, TradingBroadcast $broadcast): void;
}
