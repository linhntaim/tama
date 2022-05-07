<?php

namespace App\Support\Imports;

use App\Models\File;
use App\Support\Exceptions\FileException;
use App\Support\Exports\Export;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Foundation\Validation\Validates;
use App\Support\UnlimitedResource;
use InvalidArgumentException;

abstract class Import
{
    use Validates, UnlimitedResource;

    public const NAME = 'import';

    public abstract static function sample(): Export;

    protected int $count = 0;

    protected int $chunkSize = 0;

    protected int $chunkDataIndex = -1;

    protected int $dataIndex = -1;

    protected bool $chunkEnded = false;

    public function getName(): string
    {
        return static::NAME;
    }

    protected function filerClass(): string
    {
        return Filer::class;
    }

    protected function getFiler(File $file): Filer
    {
        return (clone $file)->setFilerClass($this->filerClass())->filer;
    }

    public function enableChunk(int $chunkSize = 1000): static
    {
        if ($chunkSize <= 0) {
            throw new InvalidArgumentException('Chunk size must be a positive integer.');
        }
        $this->chunkSize = $chunkSize;
        return $this;
    }

    public function disableChunk(): static
    {
        $this->chunkSize = 0;
        return $this;
    }

    public function chunkEnable(): bool
    {
        return !!$this->chunkSize;
    }

    public function chunkEnded(): bool
    {
        return $this->chunkEnded;
    }

    /**
     * @param Filer $filer
     * @return string|null
     * @throws FileException
     */
    protected function data($filer)
    {
        return $filer->read();
    }

    /**
     * @param Filer $filer
     * @throws FileException
     */
    protected function importBefore($filer)
    {
        $filer->openForReading()
            ->seekingLine($this->dataIndex + 1);
    }

    /**
     * @param Filer $filer
     */
    protected function importAfter($filer)
    {
        $filer->close();
    }

    /**
     * @param Filer $filer
     * @throws FileException
     */
    protected function import($filer)
    {
        $this->chunkDataIndex = -1;
        while (!is_null($data = $this->data($filer))) {
            $this->dataIndex = $filer->readingLine();
            ++$this->chunkDataIndex;
            $this->store($data);
            ++$this->count;

            if ($this->chunkSize
                && ($this->chunkDataIndex >= $this->chunkSize - 1)) {
                return;
            }
        }
        $this->chunkEnded = true;
    }

    protected abstract function store($data);

    public function count(): int
    {
        return $this->count;
    }

    public function __invoke(File $file): static
    {
        $this->unlimitedResource(function () use ($file) {
            $filer = $this->getFiler($file);
            $this->importBefore($filer);
            $this->import($filer);
            $this->importAfter($filer);
        });
        return $this;
    }
}
