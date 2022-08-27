<?php

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;

class WebServerCommand extends ForceCommand
{
    protected function handling(): int
    {
        $forced = $this->forced();
        $this->comment('Configuring web server ...');
        switch (($this->choice('Web server?', [
            'Apache',
            'NGINX',
            'IIS',
            'Other',
        ], 3))) {
            case  'Apache':
                if ($forced || !file_exists(public_path('.htaccess'))) {
                    if (copy(public_path('.htaccess.example'), public_path('.htaccess'))) {
                        $this->line('<comment>Created:</comment> .htaccess');
                    }
                    else {
                        $this->error('[.htaccess] creation failed.');
                        return $this->exitFailure();
                    }
                }
                break;
            case 'IIS':
                // TODO:
                break;
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
