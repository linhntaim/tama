<?php

namespace App\Support\Mail;

use App\Support\App;
use App\Support\ClassTrait;
use App\Support\Client\Client;
use App\Support\Client\InternalSettingsTrait;
use App\Support\Console\Artisan;
use Illuminate\Mail\Mailable as BaseMailable;
use Illuminate\Support\Facades\Log;

abstract class Mailable extends BaseMailable
{
    use ClassTrait, InternalSettingsTrait;

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

    protected function emptyAddress($property = 'to'): static
    {
        $this->{$property} = [];
        return $this;
    }

    protected function normalizeRecipient($recipient): object
    {
        if ($recipient instanceof IEmalAddress) {
            return (object)[
                'email' => $recipient->getEmailAddress(),
                'name' => $recipient->getEmailName(),
            ];
        }
        return parent::normalizeRecipient($recipient);
    }

    public function buildViewData(): array
    {
        return array_merge(parent::buildViewData(), [
            'locale' => Client::settings()->getLocale(),
            'charset' => 'utf-8',
        ]);
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
            if (!$this->jointTos) {
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

    protected function buildView(): array|string
    {
        if ($this->viewOnLocale && isset($this->view)) {
            $originView = $this->view;
            $this->view .= '.' . app()->getLocale();
            $view = parent::buildView();
            $this->view = $originView;
            return $view;
        }
        return parent::buildView();
    }
}