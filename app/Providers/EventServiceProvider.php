<?php

namespace App\Providers;

use App\Events\Trial\Event as TrialEvent;
use App\Listeners\Trial\Listener as TrialListener;
use App\Listeners\Trial\QueueableListener as TrialQueueableListener;
use App\Support\Auth\Listeners\SendEmailWelcomeNotification;
use App\Support\Facades\App;
use App\Support\Listeners\OnQueryExecuted;
use App\Trading\Events\CoinIdentificationEvent;
use App\Trading\Listeners\CoinIdentificationListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

// use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        TrialEvent::class => [
            TrialListener::class,
            TrialQueueableListener::class,
        ],
        Registered::class => [
            SendEmailWelcomeNotification::class,
            SendEmailVerificationNotification::class,
        ],
        CoinIdentificationEvent::class => [
            CoinIdentificationListener::class,
        ],
    ];

    public function register(): void
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
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
