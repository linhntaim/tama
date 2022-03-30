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
            $request = request();
            return [
                'method' => $request->method(),
                'path' => $request->path(),
                'params' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];
        }
        return null;
    }

    protected function cliContext(): ?array
    {
        if (App::runningInConsole() && !App::runningUnitTests()) {
            if ($command = Artisan::rootRunningCommand()) {
                return [
                    'command' => [
                        'class' => $command::class,
                        'name' => $command->getName(),
                        'argv' => trim(strstr(Artisan::rootRunningCommandInput(), ' ')),
                    ],
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
