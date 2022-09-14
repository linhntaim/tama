<?php

namespace App\Support\Models;

use App\Support\Filesystem\Filers\Filer;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;

/**
 * @property int $id
 * @property string $title
 * @property string $name
 * @property string $mime
 * @property string $extension
 * @property int $size
 * @property string $storage
 * @property array $options
 * @property string $file
 *
 * @property string $visibility
 * @property bool $public
 * @property string|null $url
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

    protected $visible = [
        'id',
        'title',
        'name',
        'mime',
        'extension',
        'size',
        'storage',
        'options',
        'url',
    ];

    protected $appends = [
        'url',
    ];

    protected $casts = [
        'id' => 'integer',
        'size' => 'integer',
        'options' => 'array',
    ];

    protected string $filerClass = Filer::class;

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

    public function public(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->visibility === Filesystem::VISIBILITY_PUBLIC
        );
    }

    public function url(): Attribute
    {
        return Attribute::make(
            get: fn() => match (true) {
                $this->public => $this->filer->getUrl() ?: route('file.show', ['id' => $this->id, '_download' => 1]),
                default => null,
            }
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
