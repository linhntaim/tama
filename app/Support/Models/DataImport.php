<?php

namespace App\Support\Models;

use App\Support\Imports\Import;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property File $file
 * @property Import $import
 */
class DataImport extends Model
{
    public const STATUS_IMPORTED = 1;
    public const STATUS_IMPORTING = 2;
    public const STATUS_FAILED = 3;

    protected $table = 'data_imports';

    protected $fillable = [
        'file_id',
        'status',
        'name',
        'import',
        'exception',
        'failed_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
    ];

    public function import(): Attribute
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
