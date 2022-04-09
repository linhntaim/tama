<?php

namespace App\Providers;

use App\Events\CoinIdentificationEvent;
use App\Events\TrialEvent;
use App\Listeners\CoinIdentificationListener;
use App\Listeners\TrialListener;
use App\Listeners\TrialQueueableListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        TrialEvent::class => [
            TrialListener::class,
            TrialQueueableListener::class,
        ],
        CoinIdentificationEvent::class => [
            CoinIdentificationListener::class,
        ],
    ];

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
