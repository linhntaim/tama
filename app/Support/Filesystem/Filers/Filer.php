<?php

namespace App\Support\Filesystem\Filers;

use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Storages\AwsS3Storage;
use App\Support\Filesystem\Storages\AzureBlobStorage;
use App\Support\Filesystem\Storages\ExternalStorage;
use App\Support\Filesystem\Storages\IDirectEditableStorage;
use App\Support\Filesystem\Storages\InternalStorage;
use App\Support\Filesystem\Storages\PrivateStorage;
use App\Support\Filesystem\Storages\PublicStorage;
use App\Support\Filesystem\Storages\Storage;
use App\Support\Filesystem\Storages\StorageFactory;
use Illuminate\Http\UploadedFile;
use SplFileInfo;
use SplFileObject;
use Throwable;

class Filer
{
    public const FILE_MODE_READ_ONLY = 'r';
    public const FILE_MODE_READ_AND_WRITE = 'r+';
    public const FILE_MODE_WRITE_FRESHLY = 'w';
    public const FILE_MODE_WRITE_AND_READ_FRESHLY = 'w+';
    public const FILE_MODE_WRITE_APPEND = 'a';
    public const FILE_MODE_WRITE_APPEND_AND_READ = 'a+';
    public const FILE_MODE_CREATE_THEN_WRITE = 'x';
    public const FILE_MODE_CREATE_THEN_WRITE_AND_READ = 'x+';
    public const FILE_MODE_WRITE_ONLY = 'c';
    public const FILE_MODE_WRITE_AND_READ = 'c+';

    public static function from(string|SplFileInfo|Storage $file): ?static
    {
        if ($file instanceof UploadedFile) {
            return take(new static(), function (Filer $filer) use ($file) {
                $filer->storage = (new PrivateStorage())->fromFile($file);
            });
        }
        foreach (is_url($file) ? [
            PublicStorage::class,
            config_starter('filesystems.uses.s3') ? AwsS3Storage::class : null,
            config_starter('filesystems.uses.azure') ? AzureBlobStorage::class : null,
            ExternalStorage::class,
        ] : [
            PublicStorage::class,
            PrivateStorage::class,
            InternalStorage::class,
        ] as $storageClass) {
            if (!is_null($storageClass) && ($storage = new $storageClass())->setFile($file)->has()) {
                return take(new static(), function (Filer $filer) use ($storage) {
                    $filer->storage = $storage;
                });
            }
        }
        return null;
    }

    public static function create(?string $in = null, ?string $name = null, ?string $extension = null): static
    {
        return take(new static(), function (Filer $filer) use ($in, $name, $extension) {
            $filer->storage = StorageFactory::localStorage()->create($in, $name, $extension);
        });
    }

    protected Storage $storage;

    private function __construct()
    {
    }

    public function getName(): string
    {
        return $this->storage->getName();
    }

    public function getMimeType(): string
    {
        return $this->storage->getMimeType();
    }

    public function getExtension(): string
    {
        return $this->storage->getExtension();
    }

    public function getSize(): string
    {
        return $this->storage->getSize();
    }

    public function getVisibility(): string
    {
        return $this->storage->getVisibility();
    }

    protected function moveToStorage(Storage $toStorage, ?string $in = null): static
    {
        $storage = $this->storage;
        $this->storage = $toStorage->fromFile($this->storage, $in);
        $storage->delete();
        return $this;
    }

    public function storeLocally(?string $in = null): static
    {
        return $this->moveToStorage(StorageFactory::localStorage(), $in);
    }

    public function publishPrivate(?string $in = null): static
    {
        return $this->moveToStorage(StorageFactory::privatePublishStorage(), $in);
    }

    public function publishPublic(?string $in = null): static
    {
        return $this->moveToStorage(StorageFactory::publicPublishStorage(), $in);
    }

    protected ?SplFileObject $openingFile = null;

    protected bool $skipEmpty = false;

    /**
     * @throws FileException
     */
    public function open($mode): static
    {
        if (!is_null($this->openingFile)) {
            throw new FileException('File is opening.');
        }
        if (!($this->storage instanceof IDirectEditableStorage)) {
            throw new FileException('File could not open from the storage.');
        }
        try {
            $this->openingFile = new SplFileObject($this->storage->getRealPath(), $mode);
        }
        catch (Throwable $exception) {
            throw FileException::from($exception, 'File could not open.');
        }
        return $this;
    }

    public function close(): static
    {
        $this->openingFile = null;
        return $this;
    }

    /**
     * @throws FileException
     */
    public function openForWriting(bool $fresh = true): static
    {
        return $this->open($fresh ? self::FILE_MODE_WRITE_FRESHLY : self::FILE_MODE_WRITE_APPEND);
    }

    /**
     * @param string $data
     * @return static
     * @throws FileException
     */
    public function write($data): static
    {
        if ($this->openingFile->fwrite($data) === false) {
            throw new FileException('Cannot write into the file.');
        }
        return $this;
    }

    /**
     * @param string $data
     * @return static
     * @throws FileException
     */
    public function writeln($data): static
    {
        return $this->write($data . PHP_EOL);
    }

    /**
     * @param string $data
     * @param bool $close
     * @return static
     * @throws FileException
     */
    public function writeAll($data, bool $close = true): static
    {
        $this->write($data);
        $close && $this->close();
        return $this;
    }

    /**
     * @throws FileException
     */
    public function openForReading(): static
    {
        return $this->open(self::FILE_MODE_READ_ONLY);
    }

    public function read(): mixed
    {
        if ($this->openingFile->eof()) {
            return null;
        }
        if (null_or_empty_string($read = $this->openingFile->fgets()) && $this->skipEmpty) {
            return $this->read();
        }
        return $read;
    }

    /**
     * @throws FileException
     */
    public function readAll(bool $close = true): mixed
    {
        if (($size = $this->openingFile->getSize()) === false) {
            throw new FileException('File could not retrieve its size.');
        }
        if (($read = $this->openingFile->fread($size)) === false) {
            throw new FileException('File could not read.');
        }
        $close && $this->close();
        return $read;
    }

    public function __destruct()
    {
        $this->close();
    }
}