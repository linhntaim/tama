<?php

namespace App\Trading\Console\Commands\Telegram;

trait InteractsWithTarget
{
    protected function id(): ?int
    {
        return is_null($id = $this->argument('id')) ? null : (int)$id;
    }

    protected function all(): bool
    {
        return $this->option('all');
    }
}