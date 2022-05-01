<?php

namespace App\Console\Commands\Trial;

use App\Models\UserProvider;
use App\Notifications\TrialBroadcastNotification;
use App\Notifications\TrialDatabaseNotification;
use App\Notifications\TrialMailNotification;
use App\Support\Console\Commands\Command;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;

class NotificationCommand extends Command
{
    /**
     * @throws DatabaseException|Exception
     */
    protected function handling(): int
    {
        $userProvider = new UserProvider();
        $owner = $userProvider->skipProtected()->owner();
        $system = $userProvider->skipProtected()->system();
        TrialDatabaseNotification::send($owner, $system);
        TrialBroadcastNotification::send($owner, $system);
        TrialMailNotification::send($owner, $system);
        return $this->exitSuccess();
    }
}