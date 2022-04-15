<?php

namespace App\Console\Commands\Trial;

use App\Models\UserProvider;
use App\Notifications\TrialBroadcastNotification;
use App\Notifications\TrialDatabaseNotification;
use App\Notifications\TrialMailNotification;
use App\Support\Console\Commands\Command;
use App\Support\Exceptions\DatabaseException;

class NotificationCommand extends Command
{
    /**
     * @throws DatabaseException
     */
    protected function handling(): int
    {
        $userProvider = new UserProvider();
        $owner = $userProvider->owner();
        $system = $userProvider->system();
        TrialDatabaseNotification::send($owner, $system);
        TrialBroadcastNotification::send($owner, $system);
        TrialMailNotification::send($owner, $system);
        return $this->exitSuccess();
    }
}