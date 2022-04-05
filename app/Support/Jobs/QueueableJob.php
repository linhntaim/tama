<?php

namespace App\Support\Jobs;

use App\Support\Bus\Queueable;
use App\Support\Client\InternalSettingsTrait;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class QueueableJob extends Job implements ShouldQueue
{
    use Queueable, InternalSettingsTrait;

    public function __construct()
    {
        $this->captureCurrentSettings();
    }

    public function handle()
    {
        $this->withInternalSettings(function () {
            parent::handle();
        });
    }
}