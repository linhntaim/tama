<?php

namespace App\Models;

use App\Support\Filesystem\Filers\Filer;
use App\Support\Models\Model;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;

/**
 * @property int $id
 * @property string $name
 * @property string $mime
 * @property string $extension
 * @property int $size
 * @property string $storage
 * @property array $options
 * @property string $file
 * @property string $visibility
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
        'size' => 'integer',
        'options' => 'array',
    ];

    protected $filerClass = Filer::class;

    public function setFilerClass(string $filerClass): static
    {
        $this->filerClass = $filerClass;
        return $this;
    }

    public function visibility(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->options['visibility'] ?? Filesystem::VISIBILITY_PRIVATE
        );
    }

    public function filer(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->filerClass::from($this)
        );
    }

    public function responseFile(array $headers = []): SymfonyBinaryFileResponse|SymfonyStreamedResponse
    {
        return $this->filer->responseFile($headers);
    }

    public function responseDownload(array $headers = []): SymfonyBinaryFileResponse|SymfonyStreamedResponse
    {
        return $this->filer->responseDownload($headers);
    }
}
