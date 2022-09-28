<?php

namespace App\Trading\Bots\Tests;

use App\Trading\Models\Trading;
use Illuminate\Database\Eloquent\Collection;

class TradingTest extends BotTest
{
    public function __construct(
        Collection $buyTradings,
        Collection $sellTradings,
        string     $baseAmount = '0.0',
        string     $quoteAmount = '500.0',
        float      $buyRisk = 0.0,
        float      $sellRisk = 0.0,
    )
    {
        parent::__construct(
            $baseAmount,
            $quoteAmount,
            $buyRisk,
            $sellRisk,
            $buyTradings->map(function (Trading $trading) {
                return [
                    'name' => $trading->bot,
                    'options' => $trading->botOptions,
                ];
            })->all(),
            $sellTradings->map(function (Trading $trading) {
                return [
                    'name' => $trading->bot,
                    'options' => $trading->botOptions,
                ];
            })->all()
        );
    }
}