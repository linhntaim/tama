<?php

namespace App\Support\Imports;

use App\Support\Concerns\UnlimitedResource;
use App\Support\Exceptions\FileException;
use App\Support\Exports\Export;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Foundation\Validation\Validates;
use App\Support\Models\File;
use InvalidArgumentException;

abstract class Import
{
    use Validates, UnlimitedResource;

    public const NAME = 'import';

    abstract public static function sample(): Export;

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
        return (bool)$this->chunkSize;
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
    protected function data(Filer $filer)
    {
        return $filer->read();
    }

    /**
     * @throws FileException
     */
    protected function importBefore(Filer $filer): void
    {
        $filer->openForReading()
            ->seekingLine($this->dataIndex + 1);
    }

    protected function importAfter(Filer $filer): void
    {
        $filer->close();
    }

    /**
     * @throws FileException
     */
    protected function import(Filer $filer): void
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

    /**
     * @param string $data
     * @return void
     */
    abstract protected function store($data): void;

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
