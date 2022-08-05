<?php

namespace App\Trading\Models;

use App\Support\Models\ModelProvider;

/**
 * @method TradingBroadcast createWithAttributes(array $attributes = [])
 * @method TradingBroadcast updateWithAttributes(array $attributes = [])
 * @method TradingBroadcast|null first(array $conditions = [])
 */
class TradingBroadcastProvider extends ModelProvider
{
    public string $modelClass = TradingBroadcast::class;

    public function updateStatus(int $status): TradingBroadcast
    {
        return $this->updateWithAttributes([
            'status' => $status,
        ]);
    }

    public function done(): TradingBroadcast
    {
        return $this->updateStatus(TradingBroadcast::STATUS_DONE);
    }

    public function doing(): TradingBroadcast
    {
        return $this->updateStatus(TradingBroadcast::STATUS_DOING);
    }
}
