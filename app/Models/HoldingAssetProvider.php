<?php

namespace App\Models;

use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Models\Model;
use App\Support\Models\ModelProvider;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property HoldingAsset|null $model
 * @method HoldingAsset|null model(Model|callable|int|string $model = null, bool $byUnique = true)
 * @method HoldingAsset|null first(array $conditions = [])
 * @method HoldingAsset updateOrCreateWithAttributes(array $attributes, array $values = [])
 * @method HoldingAsset updateWithAttributes(array $attributes = [])
 */
class HoldingAssetProvider extends ModelProvider
{
    public string $modelClass = HoldingAsset::class;

    /**
     * @param Holding|int $holding
     * @return Collection|HoldingAsset[]
     * @throws DatabaseException
     * @throws Exception
     */
    public function allByHolding(Holding|int $holding): Collection
    {
        return $this->all(['holding_id' => $this->retrieveKey($holding)]);
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function add(User|int $user, string $exchange, string $symbol, float $amount): ?HoldingAsset
    {
        $this->notStrict()
            ->pinModel()
            ->lockForUpdate()
            ->first([
                'user_id' => $this->retrieveKey($user),
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
                'user_id' => $this->retrieveKey($user),
                'exchange' => $exchange,
                'symbol' => $symbol,
                'amount' => $amount,
            ]);
        }
        return $this->model;
    }

    public function belongsTo(User|int $user): bool
    {
        return $this->model->user_id != $this->retrieveKey($user);
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function updateAmount(float $amount): HoldingAsset
    {
        return $this->updateWithAttributes([
            'amount' => $amount,
        ]);
    }
}
