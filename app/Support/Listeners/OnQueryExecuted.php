<?php

namespace App\Support\Listeners;

use App\Support\Facades\App;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;

class OnQueryExecuted
{
    /**
     * @param QueryExecuted $event
     */
    public function handle($event)
    {
        if(App::runningInDebug()) {
            Log::info(
                sprintf(
                    'Time: %sms. SQL: %s. Bindings: %s. Connection: %s.',
                    number_format($event->time, 2),
                    $event->sql,
                    json_encode($event->bindings),
                    $event->connectionName
                )
            );
        }
    }
}
