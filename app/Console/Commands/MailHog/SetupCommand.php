<?php

namespace App\Console\Commands\MailHog;

use App\Support\Exceptions\FileException;
use App\Support\Services\MailHogReleaseService;

class SetupCommand extends Command
{
    /**
     * @throws FileException
     */
    protected function handling(): int
    {
        if (is_array($latest = (new MailHogReleaseService())->latest())) {
            $assets = collect($latest['assets'])->keyBy('name')->all();
            switch (PHP_OS_FAMILY) {
                case 'Windows':
                    $assetKey = match (PHP_OS_ARCHITECTURE) {
                        'i386' => 'MailHog_windows_386.exe',
                        default => 'MailHog_windows_amd64.exe',
                    };
                    if (isset($assets[$assetKey])) {
                        $this->downloadBin($assets[$assetKey]['browser_download_url']);
                    }
                    break;
                case 'Linux':
                    // TODO:
                    break;
            }
        }
        return $this->exitSuccess();
    }

    /**
     * @throws FileException
     */
    protected function downloadBin(string $downloadUrl): void
    {
        $ch = curl_init($downloadUrl);
        mkdir_for_writing(dirname($this->binFile));
        $fp = fopen($this->binFile, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }
}
