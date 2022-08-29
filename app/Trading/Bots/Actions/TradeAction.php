<?php

namespace App\Trading\Bots\Actions;

use App\Trading\Bots\Bot;
use App\Trading\Bots\Data\Indication;
use App\Trading\Models\Trading;

class TradeAction implements IAction
{
    public function __invoke(Trading $trading, Bot $bot, Indication $indication): void
    {

    }
}
