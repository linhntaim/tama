<?php

namespace App\Support\Facades;

use App\Support\Console\Kernel;
use App\Support\Console\RunningCommand;
use Illuminate\Support\Facades\Artisan as BaseArtisan;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Throwable;

/**
 * @method static RunningCommand|null rootRunningCommand()
 * @method static RunningCommand|null lastRunningCommand()
 * @method static void renderThrowable(Throwable $e, $output)
 * @method static SymfonyCommand findCommand(string $name)
 *
 * @see Kernel
 */
class Artisan extends BaseArtisan
{
}
