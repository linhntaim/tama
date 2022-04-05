<?php

namespace App\Jobs;

use App\Support\Client\Client;
use App\Support\Jobs\QueueableJob;

class TestQueueableJob extends QueueableJob
{
    protected string|array|null $internalSettings = [
        'locale' => 'vi',
    ];

    protected function handling()
    {
        print_r(Client::settings()->toArray());
    }
}