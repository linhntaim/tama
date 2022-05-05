<?php

namespace App\Models;

use App\Support\Filesystem\Filers\Filer;
use App\Support\Models\Model;

/**
 * @property int $id
 * @property string $storage
 * @property string $file
 * @property Filer $filer
 */
class File extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'title',
        'name',
        'mime',
        'extension',
        'size',
        'storage',
        'options',
        'file',
    ];

    protected $casts = [
        'options' => 'array',
    ];
}
