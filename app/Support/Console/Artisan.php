<?php

namespace App\Support\Console;

use App\Console\Kernel;
use Illuminate\Support\Facades\Artisan as BaseArtisan;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Throwable;

/**
 * @method static SymfonyCommand|null rootRunningCommand()
 * @method static InputInterface|null rootRunningCommandInput()
 * @method static void renderThrowable(Throwable $e, $output)
 *
 * @see Kernel
 */
class Artisan extends BaseArtisan
{
}