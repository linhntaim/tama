<?php

namespace App\Support\Mail\Contracts;

interface ProvidesEmailAddress
{
    public function getEmailAddress(): string;

    public function getEmailName(): ?string;
}
