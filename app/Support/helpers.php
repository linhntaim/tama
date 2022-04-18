<?php

use App\Support\Client\DateTimer;
use App\Support\Client\NumberFormatter;
use App\Support\Facades\Client;
use Illuminate\Support\Str;

const JSON_READABLE = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS;
const JSON_PRETTY = JSON_READABLE | JSON_PRETTY_PRINT;

if (!function_exists('class_use')) {
    function class_use(object|string $object_or_class, string $trait): bool
    {
        return in_array($trait, class_uses_recursive($object_or_class));
    }
}

if (!function_exists('compose_filename')) {
    function compose_filename(?string $name = null, ?string $extension = null): string
    {
        return (null_or_empty_string($name) ? Str::random(40) : $name)
            . (null_or_empty_string($extension) ? '' : '.' . $extension);
    }
}

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
        if (is_object($value)) {
            return sprintf('{%s}', get_debug_type($value));
        }
        if (is_callable($value)) {
            return '{callable}';
        }
        return (string)$value;
    }
}

if (!function_exists('empty_string')) {
    function empty_string(?string $string, $trimmed = false): bool
    {
        return is_null($string) || (($trimmed ? trim($string) : $string) === '');
    }
}

if (!function_exists('extension')) {
    function extension(string $path): bool
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }
}

if (!function_exists('filled_array')) {
    function filled_array(array $array, array $default = null, $nullable = false, Closure $keyTransform = null): array
    {
        $filled = [];
        foreach ($array as $key => $value) {
            $filled[$keyTransform ? $keyTransform($key) : $key] = $value ?: $default[$key] ?? null;
        }
        return $nullable ? $filled : array_filter($filled);
    }
}

if (!function_exists('is_base64')) {
    function is_base64(string $string): bool
    {
        return base64_encode(base64_decode($string, true)) === $string;
    }
}

if (!function_exists('join_paths')) {
    function join_paths($relative = true, string ...$paths): string
    {
        return ($relative && !windows_os() ? DIRECTORY_SEPARATOR : '')
            . slash_concat(DIRECTORY_SEPARATOR, ...$paths);
    }
}

if (!function_exists('join_urls')) {
    function join_urls(string ...$urls): string
    {
        return slash_concat('/', ...$urls);
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

if (!function_exists('modify')) {
    function modify(mixed $value, ?Closure $callback = null): mixed
    {
        return is_null($callback) ? $value : $callback($value);
    }
}

if (!function_exists('name_starter')) {
    function name_starter(string $name, string $separator = '_'): string
    {
        return sprintf('%s%s%s', config_starter('app.id'), $separator, $name);
    }
}

if (!function_exists('null_or_empty_string')) {
    function null_or_empty_string(?string $string, bool $trim = true): bool
    {
        return is_null($string) || '' === ($trim ? trim($string) : $string);
    }
}

if (!function_exists('nullify_empty_array')) {
    function nullify_empty_array(array $array): ?array
    {
        return count($array) ? $array : null;
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

if (!function_exists('slash_concat')) {
    function slash_concat($slash = '/', string ...$parts): string
    {
        return implode(
            $slash,
            array_map(fn($part) => trim_more(str_replace(['\\', '/'], $slash, $part), $slash), $parts)
        );
    }
}

if (!function_exists('snaky_filled_array')) {
    function snaky_filled_array(array $array, array $default = null, $nullable = false): array
    {
        return filled_array($array, $default, $nullable, function ($key) {
            return Str::snake($key);
        });
    }
}

if (!function_exists('stringable')) {
    function stringable(mixed $value): bool
    {
        return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
    }
}

if (!function_exists('take')) {
    function take(mixed $value, ?Closure $callback = null): mixed
    {
        if (is_null($callback)) {
            return $value;
        }

        $callback($value);

        return $value;
    }
}

if (!function_exists('trim_more')) {
    function trim_more(string $string, string $characters = ''): string
    {
        return trim($string, " \t\n\r\0\x0B" . $characters);
    }
}

if (!function_exists('with_debug')) {
    function with_debug(Closure $callback, mixed ...$args): string
    {
        config(['app.debug' => true]);
        $called = value($callback, ...$args);
        config(['app.debug' => false]);
        return $called;
    }
}