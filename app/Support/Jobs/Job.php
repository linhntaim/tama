<?php

namespace App\Support\Jobs;

use App\Support\ClassTrait;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Job
{
    use ClassTrait, Dispatchable;

    protected function handleBefore()
    {
    }

    protected function handleAfter()
    {
    }

    protected abstract function handling();

    public function handle()
    {
        Log::info(sprintf('Job [%s] started.', $this->className()));
        $this->handleBefore();
        $this->handling();
        $this->handleAfter();
        Log::info(sprintf('Job [%s] ended.', $this->className()));
    }

    public function failed(?Throwable $e = null)
    {
    }
}