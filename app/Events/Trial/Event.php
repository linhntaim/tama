<?php

namespace App\Events\Trial;

use App\Support\Events\Event as BaseEvent;

class Event extends BaseEvent
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
