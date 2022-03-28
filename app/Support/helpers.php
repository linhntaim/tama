<?php

/**
 * Base
 */

if (!function_exists('copy_recursive')) {
    function copy_recursive(string $source, string $destination, $context = null): bool
    {
        if (is_file($source)) {
            return copy($source, $destination, $context);
        }

        if (is_file($destination)) {
            return false;
        }
        if (!is_dir($destination)) {
            if (false === mkdir_recursive($destination, 0777, $context)) {
                return false;
            }
        }
        $dir = opendir($source, $context);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (false === copy_recursive($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file, $context)) {
                    return false;
                }
            }
        }
        closedir($dir);
        return true;
    }
}

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

if (!function_exists('mkdir_recursive')) {
    function mkdir_recursive(string $directory, int $permissions = 0777, $context = null): bool
    {
        return mkdir($directory, $permissions, true, $context);
    }
}