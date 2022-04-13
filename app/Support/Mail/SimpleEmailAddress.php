<?php

namespace App\Support\Mail;

class SimpleEmailAddress
{
    public string $email;

    public ?string $name;

    public function __construct($email, $name = null)
    {
        $this->email = $email;
        $this->name = $name;
    }
}