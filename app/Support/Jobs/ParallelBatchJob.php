<?php

namespace App\Support\Jobs;

abstract class ParallelBatchJob extends SingleBatchJob
{
    protected bool $useLinearIndices = true;

    public function __construct(int $batchIndex = -1)
    {
        parent::__construct($batchIndex);
    }

    protected function batchArguments($batchIndex): array
    {
        return [$batchIndex];
    }

    protected abstract function batchTotal(): int;

    protected function handleBatchByIndex($batchIndex): static
    {
        static::dispatch(...$this->batchArguments($batchIndex));
        return $this;
    }

    final protected function handling()
    {
        if ($this->batchIndex() < 0) {
            $this->parallelHandleBatch();
        }
        else {
            parent::handling();
        }
    }

    protected function parallelBatchIndices(): array
    {
        $indices = range(0, $this->batchTotal() - 1);
        if (!$this->useLinearIndices) {
            shuffle($indices);
        }
        return $indices;
    }

    protected function parallelHandleBatch()
    {
        foreach ($this->parallelBatchIndices() as $batchIndex) {
            $this->handleBatchByIndex($batchIndex);
        }
    }
}
