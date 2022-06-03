<?php

namespace App\Support\Listeners;

use App\Support\Events\Event;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

abstract class QueueableListener extends Listener implements ShouldQueue
{
    use InteractsWithQueue;

    public ?string $connection = null;

    public ?string $queue = null;

    public ?int $delay = null;

    public bool $afterCommit = false;

    public ?int $tries = null;

    public ?int $maxExceptions = null;

    public ?int $backoff = null;

    public ?int $retryUntil = null;

    public ?int $timeout = null;

    public function viaConnection(): ?string
    {
        return $this->connection;
    }

    public function viaQueue(): ?string
    {
        return $this->queue;
    }

    public function backoff(): ?int
    {
        return $this->backoff;
    }

    public function retryUntil(): DateTime|int|null
    {
        return $this->retryUntil;
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function shouldQueue($event): bool
    {
        return true;
    }
}
