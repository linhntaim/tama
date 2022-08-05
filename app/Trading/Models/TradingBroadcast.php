<?php

namespace App\Trading\Models;

use App\Models\User;
use App\Support\Models\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
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
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function done(): Attribute
    {
        return Attribute::get(fn() => $this->status == self::STATUS_DONE);
    }

    public function doing(): Attribute
    {
        return Attribute::get(fn() => $this->status == self::STATUS_DOING);
    }

    public function failed(): Attribute
    {
        return Attribute::get(fn() => $this->status == self::STATUS_FAILED);
    }
}
