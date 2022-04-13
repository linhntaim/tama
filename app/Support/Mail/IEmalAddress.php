<?php

namespace App\Support\Mail;

interface IEmalAddress
{
    public function getEmailAddress(): string;

    public function getEmailName(): ?string;
}