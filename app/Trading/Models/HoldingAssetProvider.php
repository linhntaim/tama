<?php

namespace App\Trading\Models;

use App\Models\User;
use App\Support\Models\ModelProvider;
use App\Support\Models\QueryConditions\WhereCondition;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property HoldingAsset|null $model
 * @method HoldingAsset|null model(HoldingAsset|callable|int|string $model = null, bool $byUnique = true)
 * @method HoldingAsset updateOrCreateWithAttributes(array $attributes, array $values = [])
 * @method HoldingAsset updateWithAttributes(array $attributes = [])
 * @method HoldingAsset|null first(array $conditions = [])
 */
class HoldingAssetProvider extends ModelProvider
{
    public string $modelClass = HoldingAsset::class;

    /**
     * @param Holding|int $holding
     * @return Collection<int, HoldingAsset>
     */
    public function allByHolding(Holding|int $holding): Collection
    {
        return $this->all(['holding_id' => $this->retrieveKey($holding)]);
    }

    public function add(User|int $user, string $exchange, string $symbol, float $amount): HoldingAsset
    {
        $userId = $this->retrieveKey($user);
        $this->notStrict()
            ->pinModel()
            ->lockForUpdate()
            ->first([
                'user_id' => $userId,
                'exchange' => $exchange,
                'symbol' => $symbol,
            ]);

        if ($this->hasModel()) {
            $this->updateWithAttributes([
                'amount' => $this->model->amount + $amount,
            ]);
        }
        else {
            $this->createWithAttributes([
                'user_id' => $userId,
                'exchange' => $exchange,
                'symbol' => $symbol,
                'amount' => $amount,
                'order' => is_null($max = $this->max('order', ['user_id' => $userId])) ? 0 : (int)$max + 1,
            ]);
        }
        return $this->model;
    }

    public function belongsTo(User|int $user): bool
    {
        return $this->model->user_id === $this->retrieveKey($user);
    }

    public function updateAmount(float $amount): HoldingAsset
    {
        return $this->updateWithAttributes([
            'amount' => $amount,
        ]);
    }

    public function updateOrder(int $order): HoldingAsset
    {
        return $this->updateWithAttributes([
            'order' => $order,
        ]);
    }

    public function delete(): bool
    {
        $userId = $this->model->user_id;
        $order = $this->model->order;
        if (parent::delete()) {
            $this
                ->queryWhere([
                    'user_id' => $userId,
                    new WhereCondition('order', $order, '>'),
                ])
                ->decrement('order');
            return true;
        }
        return false;
    }
}
