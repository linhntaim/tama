<?php

/**
 * Base
 */

if (!function_exists('join_paths')) {
    /**
     * @param string[] $paths
     * @return string
     */
    function join_paths(...$paths): string
    {
        return implode(DIRECTORY_SEPARATOR, $paths);
    }
}

if (!function_exists('join_urls')) {
    /**
     * @param string[] $urls
     * @return string
     */
    function join_urls(...$urls): string
    {
        return implode('/', $urls);
    }
}