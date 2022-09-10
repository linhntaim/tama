<?php

namespace App\Trading\Models;

use App\Support\Models\Casts\Serialize;
use App\Support\Models\Model;
use App\Trading\Bots\Data\Indication;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property int $id
 * @property int $trading_id
 * @property string $time
 * @property Indication $indication
 * @property int $status
 * @property bool $done
 * @property bool $doing
 * @property bool $failed
 */
class TradingBroadcast extends Model
{
    public const STATUS_DONE = 1;
    public const STATUS_DOING = 2;
    public const STATUS_FAILED = 3;

    protected $table = 'trading_broadcasts';

    protected $fillable = [
        'trading_id',
        'time',
        'indication',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'trading_id' => 'integer',
        'indication' => Serialize::class,
        'status' => 'integer',
    ];

    public function done(): Attribute
    {
        return Attribute::get(fn() => $this->status === self::STATUS_DONE);
    }

    public function doing(): Attribute
    {
        return Attribute::get(fn() => $this->status === self::STATUS_DOING);
    }

    public function failed(): Attribute
    {
        return Attribute::get(fn() => $this->status === self::STATUS_FAILED);
    }
}
