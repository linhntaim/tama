<?php

namespace App\Console\Commands\Trial;

use App\Models\UserProvider;
use App\Notifications\Trial\BroadcastNotification as TrialBroadcastNotification;
use App\Notifications\Trial\DatabaseNotification as TrialDatabaseNotification;
use App\Notifications\Trial\MailNotification as TrialMailNotification;
use App\Support\Console\Commands\Command;
use App\Support\Notifications\Notification;

class NotificationCommand extends Command
{
    protected function handling(): int
    {
        $userProvider = new UserProvider();
        $owner = $userProvider->owner();
        $system = $userProvider->system();
        if (Notification::viaDatabaseEnabled()) {
            TrialDatabaseNotification::send($owner, $system);
        }
        TrialBroadcastNotification::send($owner, $system);
        TrialMailNotification::send($owner, $system);
        return $this->exitSuccess();
    }
}
