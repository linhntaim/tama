<?php

namespace App\Models;

use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Models\ModelProvider;
use App\Support\Models\QueryConditions\WhereNotInCondition;
use Throwable;

/**
 * @property Holding|null $model
 * @method Holding updateOrCreateWithAttributes(array $attributes, array $values = [])
 * @method Holding|null first(array $conditions = [])
 */
class HoldingProvider extends ModelProvider
{
    public string $modelClass = Holding::class;

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function firstByUser(User|int $user): ?Holding
    {
        return $this->first(['user_id', $this->retrieveKey($user)]);
    }

    /**
     * @throws Throwable
     */
    public function save($user, float $initial, array $assets): Holding
    {
        $this->transactionStart();
        try {
            $this->updateOrCreateWithAttributes([
                'user_id' => $this->retrieveKey($user),
            ], [
                'initial' => $initial,
            ]);
            take(new HoldingAssetProvider(), function (HoldingAssetProvider $holdingAssetProvider) use ($assets) {
                $ids = [];
                foreach ($assets as $asset) {
                    array_push($ids, $holdingAssetProvider->updateOrCreateWithAttributes([
                        'user_id' => $this->model->user_id,
                        'exchange' => $asset['exchange'],
                        'symbol' => $asset['symbol'],
                    ], [
                        'amount' => $asset['amount'],
                    ])->id);
                }
                $holdingAssetProvider->deleteAll([
                    'user_id' => $this->model->user_id,
                    new WhereNotInCondition('id', $ids),
                ]);
            });
            $this->transactionComplete();
            return $this->model;
        }
        catch (Throwable $exception) {
            $this->transactionAbort();
            throw $exception;
        }
    }
}
