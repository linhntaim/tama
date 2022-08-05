<?php

namespace App\Trading\Bots\Actions;

use App\Trading\Bots\Bot;
use App\Trading\Bots\Indication;
use App\Trading\Models\Trading;
use Illuminate\Support\Collection;

interface IAction
{
    /**
     * @param Trading $trading
     * @param Bot $bot
     * @param Indication $indication
     * @return mixed
     */
    public function __invoke(Trading $trading, Bot $bot, Indication $indication): void;
}
