<?php

namespace App\Trading\Models;

use App\Models\User;
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

    public function firstByUser(User|int $user): ?Holding
    {
        return $this->first(['user_id' => $this->retrieveKey($user)]);
    }

    /**
     * @throws Throwable
     */
    public function update(?float $initial = null, ?array $assets = null): Holding
    {
        $this->transactionStart();
        try {
            if (!is_null($initial)) {
                $this->updateWithAttributes([
                    'initial' => $initial,
                ]);
            }

            if (!is_null($assets)) {
                if (count($assets) > 0) {
                    take(new HoldingAssetProvider(), function (HoldingAssetProvider $holdingAssetProvider) use ($assets) {
                        $ids = [];
                        foreach ($assets as $index => $asset) {
                            $ids[] = $holdingAssetProvider->updateOrCreateWithAttributes([
                                'user_id' => $this->model->user_id,
                                'exchange' => $asset['exchange'],
                                'symbol' => $asset['symbol'],
                            ], [
                                'amount' => $asset['amount'],
                                'order' => $index,
                            ])->id;
                        }
                        $holdingAssetProvider->deleteAll([
                            'user_id' => $this->model->user_id,
                            new WhereNotInCondition('id', $ids),
                        ]);
                    });
                }
                else {
                    (new HoldingAssetProvider())->deleteAll(['user_id' => $this->model->user_id]);
                }
            }
            $this->transactionComplete();
            return $this->model;
        }
        catch (Throwable $exception) {
            $this->transactionAbort();
            throw $exception;
        }
    }
}
