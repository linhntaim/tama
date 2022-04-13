<?php

namespace App\Support\Mail;

use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Mail;

trait SendMailTrait
{
    protected function sendMail(Mailable $mailable, $to = null, $cc = null, $bcc = null, bool $separatedTos = false): ?SentMessage
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