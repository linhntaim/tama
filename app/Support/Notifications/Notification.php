<?php

namespace App\Support\Notifications;

use App\Support\Client\Concerns\InternalSettings;
use App\Support\Concerns\ClassHelper;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use App\Support\Mail\Mailable;
use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use App\Support\Notifications\Contracts\Notifier as NotifierContract;
use App\Support\Notifications\Contracts\ViaBroadcast;
use App\Support\Notifications\Contracts\ViaDatabase;
use App\Support\Notifications\Contracts\ViaMail;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use RuntimeException;

abstract class Notification extends BaseNotification
{
    use ClassHelper, InternalSettings;

    public static function viaDatabaseEnabled(): bool
    {
        return config_starter('notification.uses.database');
    }

    public static function sendOnDemand(array|AnonymousNotifiable $routes, mixed ...$args): void
    {
        if ($routes instanceof AnonymousNotifiable) {
            $notifiable = $routes;
        }
        else {
            $notifiable = new AnonymousNotifiable;
            foreach ($routes as $channel => $route) {
                $notifiable->route($channel, $route);
            }
        }
        $notifiable->notify(new static(...$args));
    }

    public static function send(mixed $notifiables, mixed ...$args): void
    {
        NotificationFacade::send($notifiables, new static(...$args));
    }

    protected ?NotifierContract $notifier = null;

    public function __construct(?NotifierContract $notifier = null)
    {
        $this->captureCurrentSettings();
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = Artisan::lastRunningCommand()) {
                $this->setForcedInternalSettings($runningCommand->settings());
            }
        }
        $this->notifier = $notifier;
    }

    public function via(NotifiableContract $notifiable): array|string
    {
        $via = [];
        if ($this instanceof ViaDatabase) {
            if (!self::viaDatabaseEnabled()) {
                throw new RuntimeException('Notification via database is not enabled.');
            }
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

    public function shouldSend(NotifiableContract $notifiable, string $channel): bool
    {
        return true;
    }

    public function toDatabase(NotifiableContract $notifiable): array
    {
        return ['payload' => serialize(clone $this)] + $this->dataDatabase($notifiable);
    }

    protected function dataDatabase(NotifiableContract $notifiable): array
    {
        return [];
    }

    public function toBroadcast(NotifiableContract $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
                'notifier' => $this->notifier ? [
                    'display_name' => $this->notifier->getNotifierDisplayName(),
                ] : null,
            ] + $this->dataBroadcast($notifiable));
    }

    protected function dataBroadcast(NotifiableContract $notifiable): array
    {
        return [];
    }

    public function toMail(NotifiableContract $notifiable): Mailable|MailMessage|null
    {
        return with($this->dataMail($notifiable), function ($mailable) use ($notifiable) {
            if ($mailable instanceof Mailable) {
                $mailable->to($this->dataMailRecipients($notifiable));
            }
            return $mailable;
        });
    }

    protected function dataMail(NotifiableContract $notifiable): Mailable|MailMessage|null
    {
        return null;
    }

    protected function dataMailRecipients(NotifiableContract $notifiable): array
    {
        if (is_string($recipients = $notifiable->routeNotificationFor('mail', $this))) {
            $recipients = [$recipients];
        }

        return collect($recipients)->mapWithKeys(function ($recipient, $email) {
            return is_numeric($email)
                ? [$email => (is_string($recipient) ? $recipient : $recipient->email)]
                : [$email => $recipient];
        })->all();
    }
}
