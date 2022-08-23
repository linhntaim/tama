<?php

namespace App\Support\Filesystem\Filers;

use App\Models\File;
use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Storages\AwsS3Storage;
use App\Support\Filesystem\Storages\AzureBlobStorage;
use App\Support\Filesystem\Storages\Contracts\DirectEditableStorage as DirectEditableStorageContract;
use App\Support\Filesystem\Storages\Contracts\HasInternalStorage as HasInternalStorageContract;
use App\Support\Filesystem\Storages\Contracts\HasUrlStorage as HasUrlStorageContract;
use App\Support\Filesystem\Storages\ExternalStorage;
use App\Support\Filesystem\Storages\InlineStorage;
use App\Support\Filesystem\Storages\InternalStorage;
use App\Support\Filesystem\Storages\PrivateStorage;
use App\Support\Filesystem\Storages\PublicStorage;
use App\Support\Filesystem\Storages\Storage;
use App\Support\Filesystem\Storages\StorageFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use SplFileInfo;
use SplFileObject;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;
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
    public const FILE_MODE_READS = [
        self::FILE_MODE_READ_ONLY,
        self::FILE_MODE_READ_AND_WRITE,
        self::FILE_MODE_WRITE_AND_READ_FRESHLY,
        self::FILE_MODE_WRITE_APPEND_AND_READ,
        self::FILE_MODE_CREATE_THEN_WRITE_AND_READ,
        self::FILE_MODE_WRITE_AND_READ,
    ];

    public static function from(string|SplFileInfo|Storage|Filer|File $file): ?static
    {
        if ($file instanceof File) {
            return take(new static(), function (Filer $filer) use ($file) {
                $filer->storage = take(
                    StorageFactory::create($file->storage),
                    function (Storage $storage) use ($file) {
                        $storage
                            ->setFile($file->file)
                            ->setName($file->name)
                            ->setMimeType($file->mime)
                            ->setExtension($file->extension)
                            ->setSize($file->size)
                            ->setOptions($file->options);
                    }
                );
            });
        }
        if ($file instanceof Filer) {
            return take(new static(), function (Filer $filer) use ($file) {
                $filer->storage = $file->storage;
            });
        }
        if ($file instanceof Storage) {
            return take(new static(), function (Filer $filer) use ($file) {
                $filer->storage = $file;
            });
        }
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

    /**
     * @throws FileException
     */
    public static function create(?string $in = null, ?string $name = null, ?string $extension = null): static
    {
        return take(new static(), function (Filer $filer) use ($in, $name, $extension) {
            $filer->storage = StorageFactory::localStorage()->create($in, $name, $extension);
        });
    }

    protected ?Storage $storage = null;

    protected ?SplFileObject $openingFile = null;

    protected bool $skipEmpty = false;

    protected int $readingLine = -1;

    protected int $writingLine = -1;

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

    public function getSize(): int
    {
        return $this->storage->getSize();
    }

    public function getRealPath(): ?string
    {
        return $this->internal() ? $this->storage->getRealPath() : null;
    }

    public function getUrl(): ?string
    {
        return $this->storage instanceof HasUrlStorageContract ? $this->storage->getUrl() : null;
    }

    public function getStorage(): string
    {
        return $this->storage::NAME;
    }

    public function getOptions(): array
    {
        return $this->storage->getOptions();
    }

    public function getFile(): string
    {
        return $this->storage->getFile();
    }

    public function internal(): bool
    {
        return $this->storage instanceof HasInternalStorageContract;
    }

    protected function moveToStorage(Storage $toStorage, ?string $in = null, bool $duplicate = false): static
    {
        $storage = $this->storage;
        $this->storage = $toStorage->fromFile($this->storage, $in);
        if (!$duplicate) {
            $storage->delete();
        }
        return $this;
    }

    public function storeLocally(?string $in = null, bool $duplicate = false): static
    {
        return $this->moveToStorage(StorageFactory::localStorage(), $in, $duplicate);
    }

    public function copyToLocal(?string $in = null): static
    {
        return $this->moveToStorage(StorageFactory::localStorage(), $in, true);
    }

    public function publishPrivate(?string $in = null, bool $duplicate = false): static
    {
        return $this->moveToStorage(StorageFactory::privatePublishStorage(), $in, $duplicate);
    }

    public function publishPublic(?string $in = null, bool $duplicate = false): static
    {
        return $this->moveToStorage(StorageFactory::publicPublishStorage(), $in, $duplicate);
    }

    public function publishInlinePublicly(bool $duplicate = false): static
    {
        return $this->moveToStorage(
            (new InlineStorage())
                ->setVisibility(Filesystem::VISIBILITY_PUBLIC),
            null,
            $duplicate
        );
    }

    public function publishInlinePrivately(bool $duplicate = false): static
    {
        return $this->moveToStorage(new InlineStorage(), null, $duplicate);
    }

    public function delete()
    {
        $this->storage->delete();
        $this->storage = null;
    }

    /**
     * @throws FileException
     */
    public function open($mode): static
    {
        if (!is_null($this->openingFile)) {
            throw new FileException('File is opening.');
        }
        if (!($this->storage instanceof DirectEditableStorageContract)) {
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
        if (!is_null($this->openingFile)) {
            $this->openingFile = null;
            $this->readingLine = -1;
            $this->writingLine = -1;
            clearstatcache(true, $this->storage->getRealPath());
        }
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
        ++$this->writingLine;
        if ($this->openingFile->fwrite($data) === 0) {
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
     * @param string|array|string[] $data
     * @param bool $close
     * @return static
     * @throws FileException
     */
    public function writeAll($data, bool $close = true): static
    {
        foreach ((array)$data as $line) {
            $this->writeln($line);
        }
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

    /**
     * @return string|null
     * @throws FileException
     */
    public function read()
    {
        ++$this->readingLine;
        if ($this->openingFile->eof()) {
            return null;
        }
        if (($read = $this->openingFile->fgets()) === false) {
            throw new FileException(sprintf('Could not read at line %d.', $this->readingLine()));
        }
        if ($this->skipEmpty && null_or_empty_string($read)) {
            return $this->read();
        }
        return $read;
    }

    public function seekingLine($line): static
    {
        $this->openingFile->seek($line);
        $this->readingLine = $line - 1;
        return $this;
    }

    /**
     * @return int Zero-based line index.
     */
    public function readingLine(): int
    {
        return $this->readingLine;
    }

    /**
     * @throws FileException
     */
    public function readAll(bool $close = true): array
    {
        $all = [];
        while (!is_null($read = $this->read())) {
            $all[$this->readingLine()] = $read;
        }
        $close && $this->close();
        return $all;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function responseFile(array $headers = []): SymfonyBinaryFileResponse|SymfonyStreamedResponse
    {
        return $this->storage->responseFile($headers);
    }

    public function responseDownload(array $headers = []): SymfonyBinaryFileResponse|SymfonyStreamedResponse
    {
        return $this->storage->responseDownload($headers);
    }

    public function responseStream(array $headers = []): SymfonyBinaryFileResponse|SymfonyStreamedResponse
    {
        return $this->storage->responseStream($headers);
    }

    public function responseStreamDownload(array $headers = []): SymfonyBinaryFileResponse|SymfonyStreamedResponse
    {
        return $this->storage->responseStreamDownload($headers);
    }

    public function responseContent(array $headers = []): SymfonyBinaryFileResponse|SymfonyStreamedResponse
    {
        return $this->storage->responseContent($headers);
    }

    public function responseContentDownload(array $headers = []): SymfonyBinaryFileResponse|SymfonyStreamedResponse
    {
        return $this->storage->responseContentDownload($headers);
    }
}
