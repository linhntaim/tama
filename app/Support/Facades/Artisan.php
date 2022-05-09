<?php

namespace App\Support\Facades;

use App\Console\Kernel;
use App\Support\Console\RunningCommand;
use Illuminate\Support\Facades\Artisan as BaseArtisan;
use Throwable;

/**
 * @method static RunningCommand|null rootRunningCommand()
 * @method static RunningCommand|null lastRunningCommand()
 * @method static void renderThrowable(Throwable $e, $output)
 *
 * @see Kernel
 */
class Artisan extends BaseArtisan
{
}