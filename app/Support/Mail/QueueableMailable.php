<?php

namespace App\Support\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

abstract class QueueableMailable implements ShouldQueue
{
    use Queueable, SerializesModels;
}