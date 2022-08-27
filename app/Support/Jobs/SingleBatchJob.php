<?php

namespace App\Support\Jobs;

abstract class SingleBatchJob extends BatchJob
{
    protected int $batchIndex;

    protected ?int $batchCount = null;

    public function __construct(int $batchIndex = 0)
    {
        parent::__construct();

        $this->batchIndex = $batchIndex;
    }

    final protected function batchIndex(): int
    {
        return $this->batchIndex;
    }

    protected function batchCount($items): int
    {
        return $this->batchCount ?: ($this->batchCount = parent::batchCount($items));
    }

    abstract protected function batchByIndex(int $batchIndex): iterable;

    final protected function batch(): iterable
    {
        return $this->batchByIndex($this->batchIndex());
    }

    protected function handling(): void
    {
        if ($this->batchHandled($items = $this->batch())) {
            $this->handleBatch($items);
        }
    }
}
