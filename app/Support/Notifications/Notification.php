<?php

namespace App\Support\Notifications;

use App\Support\ClassTrait;
use App\Support\Client\InternalSettings;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class Notification extends BaseNotification
{
    use ClassTrait, InternalSettings;

    public static function sendOnDemand(array $routes, mixed ...$args)
    {
        $notifiable = new AnonymousNotifiable;
        foreach ($routes as $channel => $route) {
            $notifiable->route($channel, $route);
        }
        $notifiable->notify(new static(...$args));
    }

    public static function send(mixed $notifiables, mixed ...$args)
    {
        NotificationFacade::send($notifiables, new static(...$args));
    }

    protected ?INotifier $notifier = null;

    public function __construct(?INotifier $notifier = null)
    {
        $this->captureCurrentSettings();
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = Artisan::lastRunningCommand()) {
                $this->setForcedInternalSettings($runningCommand->settings());
            }
        }
        $this->notifier = $notifier;
    }

    public function via(INotifiable $notifiable): array|string
    {
        $via = [];
        if ($this instanceof ViaDatabase) {
            $via[] = 'database';
        }
        if ($this instanceof ViaBroadcast) {
            $via[] = 'broadcast';
        }
        if ($this instanceof ViaMail) {
            $via[] = 'mail';
        }
        return $via;
    }

    public function shouldSend(INotifiable $notifiable, string $channel): bool
    {
        return true;
    }
}