<?php

namespace App\Support\Notifications;

use App\Support\Client\IHasSettings;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use App\Support\Facades\Client;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\NotificationSender as BaseNotificationSender;

class NotificationSender extends BaseNotificationSender
{
    /**
     * @param mixed $notifiables
     * @param Notification $notification
     * @param array|null $channels
     * @return void
     */
    public function sendNow($notifiables, $notification, array $channels = null)
    {
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = Artisan::lastRunningCommand()) {
                $notification->setForcedInternalSettings($runningCommand->settings());
            }
        }
        $notification->withInternalSettings(function () use ($notifiables, $notification, $channels) {
            $notifiables = $this->formatNotifiables($notifiables);

            $original = clone $notification;

            foreach ($notifiables as $notifiable) {
                if (empty($viaChannels = $channels ?: $notification->via($notifiable))) {
                    continue;
                }

                $this->withLocale($this->preferredLocale($notifiable, $notification), function () use ($viaChannels, $notifiable, $original) {
                    Client::settingsTemporary(
                        $notifiable instanceof IHasSettings ? $notifiable->getSettings() : null,
                        function () use ($viaChannels, $notifiable, $original) {
                            $notificationId = $this->generateNotificationId();

                            foreach ((array)$viaChannels as $channel) {
                                if (!($notifiable instanceof AnonymousNotifiable && $channel === 'database')) {
                                    $this->sendToNotifiable($notifiable, $notificationId, clone $original, $channel);
                                }
                            }
                        }
                    );
                });
            }
        });
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function generateNotificationId(): string
    {
        return (new DatabaseNotificationProvider())->generateUniqueId();
    }

    protected function sendToNotifiable($notifiable, $id, $notification, $channel)
    {
        $notification->id = $id;
        parent::sendToNotifiable($notifiable, $id, $notification, $channel);
    }
}
