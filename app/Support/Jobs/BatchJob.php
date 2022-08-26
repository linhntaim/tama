<?php

namespace App\Support\Jobs;

abstract class BatchJob extends QueueableJob
{
    /**
     * @return iterable
     */
    abstract protected function batch(): iterable;

    protected function batchCount($items): int
    {
        if (is_countable($items)) {
            return count($items);
        }
        $i = 0;
        foreach ($items as $ignored) {
            ++$i;
        }
        return $i;
    }

    protected function batchHandled(iterable $items): bool
    {
        return (bool)$this->batchCount($items);
    }

    protected function handling(): void
    {
        while ($this->batchHandled($items = $this->batch())) {
            $this->handleBatch($items);
        }
    }

    protected function handleBatch($items): static
    {
        $this->handleBatchItems($items);
        foreach ($items as $item) {
            $this->handleBatchItem($item);
        }
        return $this;
    }

    protected function handleBatchItems($items): void
    {
    }

    abstract protected function handleBatchItem($item): void;
}
