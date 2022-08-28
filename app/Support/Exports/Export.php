<?php

namespace App\Support\Exports;

use App\Support\Concerns\UnlimitedResource;
use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Models\File;
use InvalidArgumentException;

abstract class Export
{
    use UnlimitedResource;

    public const NAME = 'export';

    protected int $count = 0;

    protected int $chunkSize = 0;

    protected int $chunkDataIndex = -1;

    protected int $dataIndex = -1;

    protected bool $chunkEnded = false;

    public function getName(): string
    {
        return static::NAME;
    }

    public function getExtension(): ?string
    {
        return null;
    }

    protected function filerClass(): string
    {
        return Filer::class;
    }

    protected function getFiler(?File $file = null): Filer
    {
        return transform($this->filerClass(), function ($filerClass) use ($file) {
            return is_null($file)
                ? $filerClass::create(null, $this->getName(), $this->getExtension())
                : $filerClass::from($file);
        });
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

    protected function data(): mixed
    {
        return null;
    }

    /**
     * @throws FileException
     */
    protected function exportBefore(Filer $filer): void
    {
        $filer->openForWriting(false);
    }

    protected function exportAfter(Filer $filer): void
    {
        $filer->close();
    }

    /**
     * @throws FileException
     */
    protected function export(Filer $filer): void
    {
        $this->chunkDataIndex = -1;
        while (!is_null($data = $this->data())) {
            ++$this->dataIndex;
            ++$this->chunkDataIndex;
            $this->store($filer, $data);
            ++$this->count;

            if ($this->chunkSize
                && ($this->chunkDataIndex >= $this->chunkSize - 1)) {
                return;
            }
        }
        $this->chunkEnded = true;
    }

    /**
     * @throws FileException
     */
    protected function store(Filer $filer, $data): void
    {
        $filer->writeln($data);
    }

    public function count(): int
    {
        return $this->count;
    }

    public function __invoke(?File $file = null): Filer
    {
        return $this->unlimitedResource(function () use ($file) {
            $filer = $this->getFiler($file);
            $this->exportBefore($filer);
            $this->export($filer);
            $this->exportAfter($filer);
            return $filer;
        });
    }
}
