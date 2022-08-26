<?php

namespace App\Support\Models;

use App\Support\Exports\Export;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property File|null $file
 * @property Export $export
 */
class DataExport extends Model
{
    public const STATUS_EXPORTED = 1;
    public const STATUS_EXPORTING = 2;
    public const STATUS_FAILED = 3;

    protected $table = 'data_exports';

    protected $fillable = [
        'file_id',
        'status',
        'name',
        'export',
        'exception',
        'failed_at',
    ];

    protected $visible = [
        'id',
        'name',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
    ];

    public function export(): Attribute
    {
        return Attribute::make(
            get: static fn($value) => safe_unserialize($value),
            set: static fn($value) => serialize($value)
        );
    }

    public function exception(): Attribute
    {
        return Attribute::make(
            set: static fn($value) => is_null($value) ? null : (string)mb_convert_encoding($value, 'UTF-8')
        );
    }

    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }
}
