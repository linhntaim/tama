<?php

namespace App\Console\Commands\Trial;

use App\Mail\TrialMailable;
use App\Support\Console\Commands\Command;
use App\Support\Mail\SendMailTrait;

class MailCommand extends Command
{
    use SendMailTrait;

    protected function handling(): int
    {
        $this->sendMail(new TrialMailable());
        return $this->exitSuccess();
    }
}