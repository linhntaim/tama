<?php

namespace App\Support\Bus;

use Illuminate\Bus\Queueable as BaseQueueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

trait Queueable
{
    use InteractsWithQueue, BaseQueueable, SerializesModels;
}