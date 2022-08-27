<?php

namespace App\Support\Mail\Concerns;

use App\Support\Mail\Mailable;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Mail;

trait SendMail
{
    protected function sendMail(Mailable $mailable, $to = null, $cc = null, $bcc = null, bool $separatedTos = false): SentMessage|int|null
    {
        return Mail::send(
            $mailable
                ->to($to)
                ->cc($cc)
                ->bcc($bcc)
                ->separatedTos($separatedTos)
        );
    }
}
