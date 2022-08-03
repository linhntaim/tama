<?php

namespace App\Trading;

use App\Trading\Bots\Bot;
use App\Trading\Bots\OscillatingBot;
use App\Trading\Models\Bot as BotModel;
use App\Trading\Models\BotProvider;
use Illuminate\Database\Eloquent\Collection;

class Orchestrator
{
    protected function fetchBotModels(): Collection
    {
        return (new BotProvider())->all();
    }

    public function proceed()
    {
        foreach ($this->fetchBotModels() as $botModel) {
            $this->triggerBot($botModel);
        }
    }

    protected function createBotInstance(BotModel $botModel): Bot
    {
        return match ($botModel->name) {
            default => new OscillatingBot($botModel->options)
        };
    }

    protected function triggerBot(BotModel $botModel)
    {
        $this->createBotInstance($botModel)->act();
    }
}
