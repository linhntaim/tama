<?php

namespace App\Support\Jobs;

use Countable;

abstract class BatchJob extends QueueableJob
{
    /**
     * @return iterable
     */
    protected abstract function batch(): iterable;

    protected function batchCount($items): int
    {
        if ($items instanceof Countable || is_array($items)) {
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
        return !!$this->batchCount($items);
    }

    protected function handling()
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

    protected function handleBatchItems($items)
    {
    }

    protected abstract function handleBatchItem($item);
}
