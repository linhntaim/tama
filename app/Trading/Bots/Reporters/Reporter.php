<?php

namespace App\Trading\Bots\Reporters;

use App\Trading\Bots\Bot;
use App\Trading\Bots\Indication;
use Illuminate\Support\Collection;

abstract class Reporter implements IReport
{
    protected Bot $bot;

    /**
     * @param Bot $bot
     * @param Collection<int, Indication> $indications
     * @return string
     */
    public function report(Bot $bot, Collection $indications): string
    {
        $this->bot = $bot;
        return $this->print($indications);
    }

    /**
     * @param Collection<int, Indication> $indications
     * @return string
     */
    protected abstract function print(Collection $indications): string;
}
