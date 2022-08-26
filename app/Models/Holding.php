<?php

namespace App\Models;

use App\Support\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $user_id
 * @property Collection|HoldingAsset[] $assets
 * @property Collection|HoldingAsset[] $orderedAssets
 */
class Holding extends Model
{
    protected $table = 'holdings';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'initial',
    ];

    protected $visible = [
        'user_id',
        'initial',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'initial' => 'float',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(HoldingAsset::class, 'user_id', 'user_id');
    }

    public function orderedAssets(): HasMany
    {
        return $this->assets()->orderBy('order');
    }
}
