<?php

namespace App\Exceptions;

use App\Support\Console\Artisan;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\App;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function context(): array
    {
        return array_filter([
            'request' => $this->requestContent(),
            'cli' => $this->cliContext(),
        ]);
    }

    protected function requestContent(): ?array
    {
        if (!(App::runningInConsole() && !App::runningUnitTests())) {
            return request();
        }
        return null;
    }

    protected function cliContext(): ?array
    {
        if (App::runningInConsole() && !App::runningUnitTests()) {
            if ($runningCommand = Artisan::rootRunningCommand()) {
                return [
                    'command' => $runningCommand,
                ];
            }
        }
        return null;
    }

    public function renderForConsole($output, Throwable $e)
    {
        Artisan::renderThrowable($e, $output);
    }
}
