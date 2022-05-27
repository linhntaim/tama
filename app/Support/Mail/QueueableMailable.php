<?php

namespace App\Support\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

abstract class QueueableMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
}
