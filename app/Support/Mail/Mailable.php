<?php

namespace App\Support\Mail;

use App\Support\ClassTrait;
use App\Support\Client\InternalSettings;
use App\Support\Exceptions\Exception;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use App\Support\Facades\Client;
use App\Support\Notifications\INotifiable;
use Illuminate\Mail\Mailable as BaseMailable;
use Illuminate\Support\Facades\Log;

abstract class Mailable extends BaseMailable
{
    use ClassTrait, InternalSettings;

    protected string $baseTemplate = 'emails.';

    protected bool $viewOnLocale = false;

    protected bool $jointTos = true;

    public function __construct()
    {
        $this->captureCurrentSettings();
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = Artisan::lastRunningCommand()) {
                $this->setForcedInternalSettings($runningCommand->settings());
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function normalizeRecipient($recipient): object
    {
        if (is_array($recipient)) {
            if (!($count = count($recipient))) {
                throw new Exception('Email address is empty.');
            }
            if (isset($recipient['email'])) {
                return (object)$recipient;
            }
            if ($count == 1) {
                foreach ($recipient as $email => $name) {
                    if (is_string($email)) {
                        return (object)[
                            'email' => $email,
                            'name' => $name,
                        ];
                    }
                    else {
                        return (object)['email' => $email];
                    }
                }
            }

            $recipient = array_values($recipient);
            return (object)[
                'email' => $recipient[0],
                'name' => $recipient[1] ?? null,
            ];
        }
        if ($recipient instanceof INotifiable) {
            return $this->normalizeRecipient($recipient->routeNotificationFor('mail'));
        }
        if ($recipient instanceof IEmailAddress) {
            return (object)[
                'email' => $recipient->getEmailAddress(),
                'name' => $recipient->getEmailName(),
            ];
        }
        return parent::normalizeRecipient($recipient);
    }

    public function separatedTos(bool $value = true): static
    {
        $this->jointTos = !$value;
        return $this;
    }

    protected function sendBefore()
    {
    }

    protected function sendAfter()
    {
    }

    public function build()
    {
    }

    public function send($mailer)
    {
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = Artisan::lastRunningCommand()) {
                $this->setForcedInternalSettings($runningCommand->settings());
            }
        }
        $this->withInternalSettings(function () use ($mailer) {
            Log::info(sprintf('Mailable [%s] started.', $this->className()));
            $this->sendBefore();
            if ($this->jointTos) {
                $sentMessage = parent::send($mailer);
            }
            else {
                $this->sendSeparatedTos($mailer, $this->to);
            }
            $this->sendAfter();
            Log::info(sprintf('Mailable [%s] ended.', $this->className()));
            return $sentMessage ?? null;
        });
    }

    protected function sendSeparatedTos($mailer, array $tos)
    {
        foreach ($tos as $to) {
            $this->to = [$to];
            parent::send($mailer);
        }
        $this->to = $tos;
    }

    protected function buildViewTemplate($view): string
    {
        return $this->baseTemplate . $view;
    }

    protected function buildView(): array|string
    {
        if ($hasView = !is_null($originView = $this->view ?? null)) {
            $this->view = $this->buildViewTemplate($this->view);
        }
        if ($hasTextView = !is_null($originTextView = $this->textView ?? null)) {
            $this->textView = $this->buildViewTemplate($this->textView);
        }
        if ($hasMarkdown = !is_null($originMarkdown = $this->markdown ?? null)) {
            $this->markdown = $this->buildViewTemplate($this->markdown);
        }
        if ($this->viewOnLocale) {
            $locale = App::getLocale();
            if ($hasView) {
                $this->view .= '.' . $locale;
            }
            if ($hasTextView) {
                $this->textView .= '.' . $locale;
            }
            if ($hasMarkdown) {
                $this->markdown .= '.' . $locale;
            }
        }
        $view = parent::buildView();
        if ($hasView) {
            $this->view = $originView;
        }
        if ($hasTextView) {
            $this->textView = $originTextView;
        }
        if ($hasMarkdown) {
            $this->markdown = $originMarkdown;
        }
        return $view;
    }

    public function buildViewData(): array
    {
        return array_merge(parent::buildViewData(), [
            'locale' => Client::settings()->getLocale(),
            'charset' => 'utf-8',
        ]);
    }
}