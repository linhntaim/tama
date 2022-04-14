<?php

namespace App\Console\Commands\Trial;

use App\Notifications\TrialDatabaseNotification;
use App\Notifications\TrialMailNotification;
use App\Support\Console\Commands\Command;

class NotificationCommand extends Command
{
    protected function handling(): int
    {
//        TrialDatabaseNotification::send([]);
        TrialMailNotification::sendOnDemand(['mail' => 'linhnt.aim@outlook.com']);
        return $this->exitSuccess();
    }
}