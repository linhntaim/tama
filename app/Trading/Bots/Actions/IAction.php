<?php

namespace App\Trading\Bots\Actions;

use App\Trading\Bots\Bot;
use App\Trading\Bots\Data\Indication;
use App\Trading\Models\Trading;

interface IAction
{
    /**
     * @param Trading $trading
     * @param Bot $bot
     * @param Indication $indication
     * @return void
     */
    public function __invoke(Trading $trading, Bot $bot, Indication $indication): void;
}
