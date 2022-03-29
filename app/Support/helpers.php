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

if (!function_exists('describe_var')) {
    function describe_var(mixed $value, int $depth = 1, int $maxShownItems = 3): string
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_string($value)) {
            return sprintf('\'%s\'', str_replace('\'', '\\\'', $value));
        }
        if (is_array($value)) {
            return $depth
                ? sprintf('[%s]', implode(', ', (function ($array) use ($maxShownItems) {
                    if (count($array) <= $maxShownItems) {
                        return $array;
                    }
                    $sliced = array_slice($array, 0, $maxShownItems);
                    $sliced[] = '...';
                    return $sliced;
                })(array_map(fn($item) => describe_var($item, $depth - 1), $value))))
                : '{array}';
        }
        if (is_resource($value)) {
            return '{resource}';
        }
        if (is_callable($value)) {
            return '{callable}';
        }
        if (is_object($value)) {
            return sprintf('{%s}', get_debug_type($value));
        }
        return (string)$value;
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