<?php

namespace App\Support\Http\Concerns;

trait Abort
{
    protected function abort401(string $message = 'Not authenticated.'): void
    {
        abort(401, $message);
    }

    protected function abort403(string $message = 'Not authorized.'): void
    {
        abort(403, $message);
    }

    protected function abort404(string $message = 'Not found.'): void
    {
        abort(404, $message);
    }

    protected function abort500(string $message = 'Internal server error.'): void
    {
        abort(500, $message);
    }
}
