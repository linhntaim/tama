<?php

namespace App\Support\Mail;

class SimpleEmailAddress implements IEmailAddress
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
