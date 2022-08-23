<?php

namespace App\Support\Mail;

use App\Support\Mail\Contracts\ProvidesEmailAddress;

class SimpleEmailAddress implements ProvidesEmailAddress
{
    public string $email;

    public ?string $name;

    public function __construct($email, $name = null)
    {
        $this->email = $email;
        $this->name = $name;
    }

    public function getEmailAddress(): string
    {
        return $this->email;
    }

    public function getEmailName(): ?string
    {
        return $this->name;
    }
}
