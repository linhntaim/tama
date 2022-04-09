<?php

/**
 * Base
 */

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;

class WebServerCommand extends ForceCommand
{
    protected function handling(): int
    {
        $forced = $this->forced();
        if ($forced || !file_exists(public_path('.htaccess'))) {
            if (copy(public_path('.htaccess.example'), public_path('.htaccess'))) {
                $this->line('<comment>Created:</comment> .htaccess');
            }
            else {
                $this->error('[.htaccess] creation failed.');
                return $this->exitFailure();
            }
        }
        if ($forced || !file_exists(public_path('.htpasswd'))) {
            if (copy(public_path('.htpasswd.example'), public_path('.htpasswd'))) {
                $this->line('<comment>Created:</comment> .htpasswd');
            }
            else {
                $this->error('[.htpasswd] creation failed.');
                return $this->exitFailure();
            }
        }
        if ($forced || !file_exists(public_path('web.config'))) {
            if (copy(public_path('web.config.example'), public_path('web.config'))) {
                $this->line('<comment>Created:</comment> web.config');
            }
            else {
                $this->error('[web.config] creation failed.');
                return $this->exitFailure();
            }
        }
        if ($forced || !file_exists(public_path('robots.txt'))) {
            if (copy(public_path('robots.txt.example'), public_path('robots.txt'))) {
                $this->line('<comment>Created:</comment> robots.txt');
            }
            else {
                $this->error('[robots.txt] creation failed.');
                return $this->exitFailure();
            }
        }
        $this->info('Web server has been configured.');
        return $this->exitSuccess();
    }
}