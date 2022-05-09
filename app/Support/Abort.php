<?php

namespace App\Support;

trait Abort
{
    protected function abort404(string $message = 'Not found.')
    {
        abort(404, $message);
    }
}
