<?php

namespace App\Events;

use App\Support\Events\Event;

class TrialEvent extends Event
{
    protected string $capturedDate;

    public function __construct()
    {
        parent::__construct();

        $this->capturedDate = $this->date();
    }

    public function capturedDate(): string
    {
        return $this->capturedDate;
    }

    public function date(): ?string
    {
        return date_timer()->compound('longDate', ' ', 'longTime');
    }
}