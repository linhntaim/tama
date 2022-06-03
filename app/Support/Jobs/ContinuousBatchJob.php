<?php

namespace App\Support\Jobs;

abstract class ContinuousBatchJob extends SingleBatchJob
{
    public function __construct(int $batchIndex = 0)
    {
        parent::__construct($batchIndex);
    }

    protected function nextBatchArguments(): array
    {
        return [$this->batchIndex() + 1];
    }

    final protected function handleNextBatch(): static
    {
        static::dispatch(...$this->nextBatchArguments());
        return $this;
    }

    final protected function handleBatch($items): static
    {
        return parent::handleBatch($items)->handleNextBatch();
    }
}
