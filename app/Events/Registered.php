<?php

namespace App\Events;

use App\Support\Events\Event;
use App\Support\Models\User;

class Registered extends Event
{
    public User $user;

    public string $password;

    public function __construct(User $user, string $password)
    {
        parent::__construct();

        $this->user = $user;
        $this->password = $password;
    }
}
