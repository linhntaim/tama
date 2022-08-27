<?php

namespace App\Support\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class QueueableNotification extends Notification implements ShouldQueue
{
    use Queueable;
}
