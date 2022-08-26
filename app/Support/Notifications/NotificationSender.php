<?php

namespace App\Support\Notifications;

use App\Support\Client\Contracts\HasSettings;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use App\Support\Facades\Client;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\NotificationSender as BaseNotificationSender;
use Illuminate\Support\Str;

class NotificationSender extends BaseNotificationSender
{
    /**
     * @param mixed $notifiables
     * @param Notification $notification
     * @param array|null $channels
     * @return void
     */
    public function sendNow($notifiables, $notification, array $channels = null): void
    {
        if (App::runningSolelyInConsole() && !is_null($runningCommand = Artisan::lastRunningCommand())) {
            $notification->setForcedInternalSettings($runningCommand->settings());
        }
        $notification instanceof Notification
            ? $notification->withInternalSettings(fn() => $this->sendNowWithSettings($notifiables, $notification, $channels))
            : $this->sendNowWithSettings($notifiables, $notification, $channels);
    }

    protected function sendNowWithSettings($notifiables, $notification, array $channels = null): void
    {
        $notifiables = $this->formatNotifiables($notifiables);

        $original = clone $notification;

        foreach ($notifiables as $notifiable) {
            if (empty($viaChannels = $channels ?: (array)$notification->via($notifiable))) {
                continue;
            }

            $this->withLocale($this->preferredLocale($notifiable, $notification), function () use ($viaChannels, $notifiable, $original) {
                Client::settingsTemporary(
                    $notifiable instanceof HasSettings ? $notifiable->getSettings() : null,
                    function () use ($viaChannels, $notifiable, $original) {
                        $notificationId = $this->generateNotificationId();

                        foreach ($viaChannels as $channel) {
                            if (!($notifiable instanceof AnonymousNotifiable && $channel === 'database')) {
                                $this->sendToNotifiable($notifiable, $notificationId, clone $original, $channel);
                            }
                        }
                    }
                );
            });
        }
    }

    protected function generateNotificationId(): string
    {
        return Notification::viaDatabaseEnabled()
            ? (new DatabaseNotificationProvider())->generateUniqueId()
            : Str::uuid()->toString();
    }

    protected function sendToNotifiable($notifiable, $id, $notification, $channel): void
    {
        $notification->id = $id;
        parent::sendToNotifiable($notifiable, $id, $notification, $channel);
    }
}
