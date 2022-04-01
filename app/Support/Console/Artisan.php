<?php

/**
 * Base
 */

namespace App\Support\Console;

use App\Console\Kernel;
use Illuminate\Support\Facades\Artisan as BaseArtisan;
use Throwable;

/**
 * @method static RunningCommand|null rootRunningCommand()
 * @method static void renderThrowable(Throwable $e, $output)
 *
 * @see Kernel
 */
class Artisan extends BaseArtisan
{
}