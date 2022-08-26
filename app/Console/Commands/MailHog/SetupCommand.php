<?php

namespace App\Console\Commands\MailHog;

use App\Support\Services\MailHogReleaseService;
use RuntimeException;

class SetupCommand extends Command
{
    protected function handling(): int
    {
        if (is_array($latest = (new MailHogReleaseService())->latest())) {
            $assets = collect($latest['assets'])->keyBy('name')->all();
            switch (PHP_OS_FAMILY) {
                case 'Windows':
                    $assetKey = match (PHP_OS_ARCHITECTURE) {
                        'i386' => 'MailHog_windows_386.exe',
                        default => 'MailHog_windows_amd64.exe'
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

    protected function downloadBin(string $downloadUrl): void
    {
        $ch = curl_init($downloadUrl);
        if (!is_dir($dir = dirname($this->binFile)) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Cannot create the directory "%s"', $dir));
        }
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
