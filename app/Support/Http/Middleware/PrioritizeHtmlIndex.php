<?php

namespace App\Support\Http\Middleware;

use App\Support\Http\Request;
use App\Support\Http\Responses;
use Closure;

class PrioritizeHtmlIndex
{
    use Responses;

    protected function htmlIndexFile(Request $request): ?string
    {
        $currentPath = concat_paths(true, $request->getRequestUri());
        $htmlIndexConfig = config_starter('html_index');
        $htmlIndexFiles = $htmlIndexConfig['files'];
        $htmlIndexPaths = $htmlIndexConfig['paths'];
        array_unshift($htmlIndexPaths, '');
        foreach ($htmlIndexPaths as $htmlIndexPath) {
            if (($htmlIndexPath = concat_paths(true, $htmlIndexPath)) == $currentPath
                || str($currentPath)->startsWith($htmlIndexPath)) {
                foreach ($htmlIndexFiles as $htmlIndexFile) {
                    if (is_file($htmlIndexFile = public_path(concat_paths(true, $htmlIndexPath, $htmlIndexFile)))) {
                        return $htmlIndexFile;
                    }
                }
            }
        }
        return null;
    }

    public function handle(Request $request, Closure $next)
    {
        if ($htmlIndexFile = $this->htmlIndexFile($request)) {
            return $this->responseFile($request, $htmlIndexFile);
        }
        return $next($request);
    }
}
