<?php

namespace App\Support\Mail;

interface IEmailAddress
{
    public function getEmailAddress(): string;

    public function getEmailName(): ?string;
}