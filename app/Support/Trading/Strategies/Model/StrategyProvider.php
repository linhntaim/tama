<?php

namespace App\Support\Trading\Strategies\Model;

use App\Support\Models\ModelProvider;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Strategy[] all(array $conditions = [])
 */
class StrategyProvider extends ModelProvider
{
    public string $modelClass = Strategy::class;

    public function allByUser($user): Collection
    {
        return $this->all(['user_id' => $this->retrieveKey($user)]);
    }
}
