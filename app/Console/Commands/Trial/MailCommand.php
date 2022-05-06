<?php

namespace App\Console\Commands\Trial;

use App\Mail\Trial\LocaleViewMailable as TrialLocaleViewMailable;
use App\Mail\Trial\LocaleViewQueueableMailable as TrialLocaleViewQueueableMailable;
use App\Mail\Trial\Mailable as TrialMailable;
use App\Mail\Trial\QueueableMailable as TrialQueueableMailable;
use App\Mail\Trial\ViewMailable as TrialViewMailable;
use App\Mail\Trial\ViewQueueableMailable as TrialViewQueueableMailable;
use App\Support\Console\Commands\Command;
use App\Support\Mail\SendMail;
use App\Support\Mail\SimpleEmailAddress;

class MailCommand extends Command
{
    public $signature = '{to} {--name=}';

    use SendMail;

    protected function handling(): int
    {
        $to = new SimpleEmailAddress($this->argument('to'), $this->option('name'));
        $this->sendMail(new TrialMailable(), $to);
        $this->sendMail(new TrialQueueableMailable(), $to);
        $this->sendMail(new TrialViewMailable(), $to);
        $this->sendMail(new TrialViewQueueableMailable(), $to);
        $this->sendMail(new TrialLocaleViewMailable(), $to);
        $this->sendMail(new TrialLocaleViewQueueableMailable(), $to);
        return $this->exitSuccess();
    }
}
