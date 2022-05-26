<?php

namespace App\Providers;

use App\Events\Trial\Event as TrialEvent;
use App\Listeners\OnQueryExecuted;
use App\Listeners\SendEmailWelcomeNotification;
use App\Listeners\Trial\Listener as TrialListener;
use App\Listeners\Trial\QueueableListener as TrialQueueableListener;
use App\Support\Facades\App;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailWelcomeNotification::class,
            SendEmailVerificationNotification::class,
        ],
        TrialEvent::class => [
            TrialListener::class,
            TrialQueueableListener::class,
        ],
    ];

    public function register()
    {
        if (App::runningInDebug()) {
            $this->listen[QueryExecuted::class] = [
                OnQueryExecuted::class,
            ];
        }
        parent::register();
    }

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
