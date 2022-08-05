<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Actions\IAction;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use Illuminate\Database\Eloquent\Collection;

class BotOrchestrator
{
    /**
     * @var IAction[]
     */
    protected array $actions = [];

    /**
     * @param IAction|IAction[] $action
     * @return $this
     */
    public function registerAction(IAction|array $action): static
    {
        if ($action instanceof IAction) {
            return $this->registerAction([$action]);
        }
        array_push($this->actions, ...$action);
        return $this;
    }

    /**
     * @return Collection|Trading[]
     */
    protected function fetchTradings(): Collection|array
    {
        return (new TradingProvider())->all();
    }

    public function proceed()
    {
        foreach ($this->fetchTradings() as $trading) {
            $this->broadcastActions($trading);
        }
    }

    protected function broadcastActions(Trading $trading)
    {
        (new BotBroadcaster($trading, $this->actions))->broadcast();
    }
}
