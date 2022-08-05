<?php

namespace App\Trading\Bots\Reporters;

use App\Trading\Bots\Bot;
use Illuminate\Support\Collection;

interface IReport
{
    public function report(Bot $bot, Collection $indications): string;
}
