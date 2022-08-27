<?php

namespace App\Support\Services;

class MailHogReleaseService extends Service
{
    protected string $baseUrl = 'https://api.github.com/repos/mailhog/MailHog/releases';

    public function latest(): bool|array
    {
        return $this->get('latest')->response();
    }
}
