<?php

/**
 * Base
 */

use App\Support\Client\Client;
use App\Support\Client\DateTimer;
use App\Support\Client\NumberFormatter;

const JSON_READABLE = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS;
const JSON_PRETTY = JSON_READABLE | JSON_PRETTY_PRINT;

if (!function_exists('config_starter')) {
    function config_starter(array|string|null $key = null, $default = null): mixed
    {
        if (is_null($key)) {
            $key = 'starter';
        }
        elseif (is_string($key)) {
            $key = "starter.$key";
        }
        elseif (is_array($key)) {
            $values = $key;
            $key = [];
            foreach ($values as $k => $v) {
                $key["starter.$k"] = $v;
            }
        }
        return config($key, $default);
    }
}

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

if (!function_exists('date_timer')) {
    function date_timer(): DateTimer
    {
        return Client::dateTimer();
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
        return implode(
            DIRECTORY_SEPARATOR,
            array_map(fn($path) => trim_more(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR), $paths)
        );
    }
}

if (!function_exists('join_urls')) {
    /**
     * @param string[] $urls
     * @return string
     */
    function join_urls(...$urls): string
    {
        return implode(
            '/',
            array_map(fn($url) => trim_more(str_replace(['\\'], '/', $url), '/'), $urls)
        );
    }
}

if (!function_exists('json_encode_pretty')) {
    function json_encode_pretty(mixed $value, int $depth = 512): string|false
    {
        return json_encode($value, JSON_PRETTY, $depth);
    }
}

if (!function_exists('json_encode_readable')) {
    function json_encode_readable(mixed $value, int $depth = 512): string|false
    {
        return json_encode($value, JSON_READABLE, $depth);
    }
}

if (!function_exists('json_decode_array')) {
    function json_decode_array(string $json, int $depth = 512, int $flags = 0): ?array
    {
        return is_array($array = json_decode($json, true, $depth, $flags)) ? $array : null;
    }
}

if (!function_exists('ltrim_more')) {
    function ltrim_more(string $string, string $characters = ''): string
    {
        return ltrim($string, " \t\n\r\0\x0B" . $characters);
    }
}

if (!function_exists('mkdir_recursive')) {
    function mkdir_recursive(string $directory, int $permissions = 0777, $context = null): bool
    {
        return mkdir($directory, $permissions, true, $context);
    }
}

if (!function_exists('name_starter')) {
    function name_starter(string $name, string $separator = '_'): string
    {
        return sprintf('%s%s%s', config_starter('app.id'), $separator, $name);
    }
}

if (!function_exists('number_formatter')) {
    function number_formatter(): NumberFormatter
    {
        return Client::numberFormatter();
    }
}

if (!function_exists('rtrim_more')) {
    function rtrim_more(string $string, string $characters = ''): string
    {
        return rtrim($string, " \t\n\r\0\x0B" . $characters);
    }
}

if (!function_exists('trim_more')) {
    function trim_more(string $string, string $characters = ''): string
    {
        return trim($string, " \t\n\r\0\x0B" . $characters);
    }
}