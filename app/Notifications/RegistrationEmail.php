<?php

namespace App\Notifications;

use App\Support\Mail\Mailable;
use App\Support\Notifications\INotifiable;
use App\Support\Notifications\INotifier;
use App\Support\Notifications\Notification;
use App\Support\Notifications\ViaMail;
use Closure;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class RegistrationEmail extends Notification implements ViaMail
{
    public static ?Closure $createUrlCallback;

    public string $password;

    public function __construct(string $password, ?INotifier $notifier = null)
    {
        parent::__construct($notifier);

        $this->password = $password;
    }

    protected function loginUrl(INotifiable $notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        return url(route('login', [], false));
    }

    public function dataMailable(INotifiable $notifiable): Mailable|MailMessage|null
    {
        return (new MailMessage)
            ->subject(Lang::get('New registration'))
            ->line(Lang::get('You have complete your registration.'))
            ->action(Lang::get('Login'), $this->loginUrl($notifiable))
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }
}
