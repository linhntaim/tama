<?php

use App\Support\Client\DateTimer;
use App\Support\Client\NumberFormatter;
use App\Support\Exceptions\FileException;
use App\Support\Facades\Client;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\VarDumper\VarDumper;

const BC_DEFAULT_SCALE = 18;
const DATE_DEFAULT = 'Y-m-d H:i:s';
const DATE_DATABASE = DATE_DEFAULT;
const JSON_READABLE = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS;
const JSON_PRETTY = JSON_READABLE | JSON_PRETTY_PRINT;

if (!defined('PHP_OS_ARCHITECTURE')) {
    define('PHP_OS_ARCHITECTURE', strtolower(php_uname('m')));
}

if (!function_exists('array_associated_map')) {
    function array_associated_map(array $array, array $associatedKeys): array
    {
        $associatedArray = [];
        $array = array_values($array);
        $i = 0;
        foreach ($associatedKeys as $index => $associatedKey) {
            if (is_int($index) && is_string($associatedKey)) {
                $associatedArray[$associatedKey] = $array[$i];
            }
            elseif (is_string($index) && is_callable($associatedKey)) {
                $associatedArray[$index] = $associatedKey($array[$i]);
            }
            ++$i;
        }
        return $associatedArray;
    }
}

if (!function_exists('call_if')) {
    function call_if(bool $condition, Closure $callback, mixed ...$args): bool
    {
        if ($condition) {
            value($callback, ...$args);
        }
        return $condition;
    }
}

if (!function_exists('call_unless')) {
    function call_unless(bool $condition, Closure $callback, mixed ...$args): bool
    {
        call_if(!$condition, $callback, ...$args);
        return $condition;
    }
}

if (!function_exists('class_use')) {
    function class_use(object|string $object_or_class, string $trait): bool
    {
        return in_array($trait, class_uses_recursive($object_or_class), true);
    }
}

if (!function_exists('compose_filename')) {
    function compose_filename(?string $name = null, ?string $extension = null): string
    {
        return (null_or_empty_string($name) ? Str::random(40) : $name)
            . (null_or_empty_string($extension) ? '' : '.' . $extension);
    }
}

if (!function_exists('concat_paths')) {
    function concat_paths($relative = true, string ...$paths): string
    {
        return ($relative || windows_os() ? '' : DIRECTORY_SEPARATOR)
            . concat_with_slash(DIRECTORY_SEPARATOR, ...$paths);
    }
}

if (!function_exists('concat_urls')) {
    function concat_urls(string ...$urls): string
    {
        return concat_with_slash('/', ...$urls);
    }
}

if (!function_exists('concat_with_slash')) {
    function concat_with_slash($slash = '/', string ...$parts): string
    {
        return implode(
            $slash,
            array_map(static fn($part) => trim_more(str_replace(['\\', '/'], $slash, $part), $slash), $parts)
        );
    }
}

if (!function_exists('concat_with_comma')) {
    function concat_with_comma(string ...$parts): string
    {
        return implode(
            ',',
            $parts
        );
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
    /**
     * @throws FileException
     */
    function copy_recursive(string $source, string $destination, $context = null): bool
    {
        if (is_file($source)) {
            return copy($source, $destination, $context);
        }

        if (is_file($destination)) {
            return false;
        }
        if (!is_dir($destination) && false === mkdir_recursive($destination, 0777, $context)) {
            return false;
        }
        $dir = opendir($source, $context);
        while (false !== ($file = readdir($dir))) {
            if ($file !== '.' && $file !== '..'
                && false === copy_recursive($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file, $context)) {
                return false;
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
                ? sprintf('[%s]', implode(', ', (static function ($array) use ($maxShownItems) {
                    if (count($array) <= $maxShownItems) {
                        return $array;
                    }
                    $sliced = array_slice($array, 0, $maxShownItems);
                    $sliced[] = '...';
                    return $sliced;
                })(array_map(static fn($item) => describe_var($item, $depth - 1), $value))))
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

if (!function_exists('dd_with_headers')) {
    function dd_with_headers(array $headers, ...$vars): void
    {
        if (!in_array(PHP_SAPI, ['cli', 'phpdbg'], true) && !headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            foreach ($headers as $name => $value) {
                header(sprintf('%s: %s', $name, $value));
            }
        }

        out(...$vars);
        exit(1);
    }
}

if (!function_exists('dd_with_cors')) {
    function dd_with_cors(...$vars): void
    {
        dd_with_headers([
            'Access-Control-Allow-Origin' => '*',
        ], ...$vars);
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

if (!function_exists('from_ini_filesize')) {
    function from_ini_filesize(string $size): int
    {
        if ('' === $size) {
            return 0;
        }
        $size = strtolower($size);
        $int = ltrim($size, '+');
        if (str_starts_with($int, '0x')) {
            $int = intval($int, 16);
        }
        elseif (str_starts_with($int, '0')) {
            $int = intval($int, 8);
        }
        else {
            $int = (int)$int;
        }
        switch (substr($size, -1)) {
            case 't':
                $int *= 1024;
            // no break
            case 'g':
                $int *= 1024;
            // no break
            case 'm':
                $int *= 1024;
            // no break
            case 'k':
                $int *= 1024;
        }
        return $int;
    }
}

if (!function_exists('gcd')) {
    function gcd(int $num1, int $num2): int
    {
        return $num2 === 0 ? $num1 : gcd($num2, $num1 % $num2);
    }
}

if (!function_exists('guess_extension')) {
    function guess_extension(string $mimeType): string
    {
        return MimeTypes::getDefault()->getExtensions($mimeType)[0] ?? '';
    }
}

if (!function_exists('guess_mime_type')) {
    function guess_mime_type(string $extension): string
    {
        return MimeTypes::getDefault()->getMimeTypes($extension)[0] ?? '';
    }
}

if (!function_exists('int_eq')) {
    function int_eq(float|int $value): bool
    {
        return is_int($value) || num_eq($value, (int)$value);
    }
}

if (!function_exists('int_floor')) {
    function int_floor(float|int $num): int
    {
        return num_floor($num, 0);
    }
}

if (!function_exists('is_base64')) {
    function is_base64($string): bool
    {
        return is_string($string)
            && base64_encode(base64_decode($string, true)) === $string;
    }
}

if (!function_exists('is_url')) {
    function is_url($string): bool
    {
        return !Validator::make(['url' => $string], ['url' => 'string|url'])->fails();
    }
}

if (!function_exists('json_encode_pretty')) {
    function json_encode_pretty(mixed $value, int $flags = 0, int $depth = 512): string|false
    {
        return json_encode($value, $flags | JSON_PRETTY, $depth);
    }
}

if (!function_exists('json_encode_readable')) {
    function json_encode_readable(mixed $value, int $flags = 0, int $depth = 512): string|false
    {
        return json_encode($value, $flags | JSON_READABLE, $depth);
    }
}

if (!function_exists('json_decode_array')) {
    function json_decode_array(string|bool|null $json, int $depth = 512, int $flags = 0): ?array
    {
        return is_array($array = json_decode($json, true, $depth, $flags)) ? $array : null;
    }
}

if (!function_exists('lcm')) {
    function lcm(int $num1, int $num2): int
    {
        return ($num1 * $num2) / gcd($num1, $num2);
    }
}

if (!function_exists('ltrim_more')) {
    function ltrim_more(string $string, string $characters = ''): string
    {
        return ltrim($string, " \t\n\r\0\x0B" . $characters);
    }
}

if (!function_exists('mkdir_recursive')) {
    /**
     * @throws FileException
     */
    function mkdir_recursive(string $directory, int $permissions = 0777, $context = null): bool
    {
        if (!is_dir($directory) && false === @mkdir($directory, $permissions, true, $context) && !is_dir($directory)) {
            throw new FileException(sprintf('Unable to create the "%s" directory.', $directory));
        }
        return true;
    }
}

if (!function_exists('mkdir_for_writing')) {
    /**
     * @throws FileException
     */
    function mkdir_for_writing(string $directory, $context = null): bool
    {
        mkdir_recursive($directory, 0777, $context);
        if (!is_writable($directory)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory.', $directory));
        }
        return true;
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

if (!function_exists('num_add')) {
    function num_add(float|int|string $num1, float|int|string $num2, ?int $scale = null): string
    {
        return bcadd(num_std($num1), num_std($num2), $scale);
    }
}

if (!function_exists('num_avg')) {
    /**
     * @param float[]|int[]|string[] $nums
     * @param int|null $scale
     * @return string
     */
    function num_avg(array $nums, ?int $scale = null): string
    {
        $sum = 0;
        foreach ($nums as $num) {
            $sum = num_add($sum, $num, $scale);
        }
        return num_div($sum, count($nums), $scale);
    }
}

if (!function_exists('num_comp')) {
    function num_comp(float|int|string $num1, float|int|string $num2, ?int $scale = null): int
    {
        return bccomp(num_std($num1), num_std($num2), $scale);
    }
}

if (!function_exists('num_div')) {
    function num_div(float|int|string $num1, float|int|string $num2, ?int $scale = null): string
    {
        return bcdiv(num_std($num1), num_std($num2), $scale);
    }
}

if (!function_exists('num_eq')) {
    function num_eq(float|int|string $num1, float|int|string $num2, ?int $scale = null): bool
    {
        return num_comp($num1, $num2, $scale) === 0;
    }
}

if (!function_exists('num_floor')) {
    function num_floor(int|float|string $num, ?int $precision = null): string
    {
        return bcadd(num_std($num), 0, $precision);
    }
}

if (!function_exists('num_gt')) {
    function num_gt(float|int|string $num1, float|int|string $num2, ?int $scale = null): bool
    {
        return num_comp($num1, $num2, $scale) === 1;
    }
}

if (!function_exists('num_gte')) {
    function num_gte(float|int|string $num1, float|int|string $num2, ?int $scale = null): bool
    {
        return num_comp($num1, $num2, $scale) >= 0;
    }
}

if (!function_exists('num_lt')) {
    function num_lt(float|int|string $num1, float|int|string $num2, ?int $scale = null): bool
    {
        return num_comp($num1, $num2, $scale) === -1;
    }
}

if (!function_exists('num_lte')) {
    function num_lte(float|int|string $num1, float|int|string $num2, ?int $scale = null): bool
    {
        return num_comp($num1, $num2, $scale) <= 0;
    }
}

if (!function_exists('num_max')) {
    function num_max(float|int|string $num1, float|int|string $num2, ?int $scale = null): string
    {
        return num_gte($num1, $num2, $scale) ? $num1 : $num2;
    }
}

if (!function_exists('num_min')) {
    function num_min(float|int|string $num1, float|int|string $num2, ?int $scale = null): string
    {
        return num_lte($num1, $num2, $scale) ? $num1 : $num2;
    }
}

if (!function_exists('num_mod')) {
    function num_mod(float|int|string $num1, float|int|string $num2, ?int $scale = null): string
    {
        return bcmod(num_std($num1), num_std($num2), $scale);
    }
}

if (!function_exists('num_mul')) {
    function num_mul(float|int|string $num1, float|int|string $num2, ?int $scale = null): string
    {
        return bcmul(num_std($num1), num_std($num2), $scale);
    }
}

if (!function_exists('num_ne')) {
    function num_ne(float|int|string $num1, float|int|string $num2, ?int $scale = null): bool
    {
        return !num_eq($num1, $num2, $scale);
    }
}

if (!function_exists('num_neg')) {
    function num_neg(float|int|string $num, ?int $scale = null): string
    {
        return num_sub(0, num_std($num), $scale);
    }
}

if (!function_exists('num_pow')) {
    function num_pow(float|int|string $num, int $exponent, ?int $scale = null): string
    {
        return bcpow(num_std($num), $exponent, $scale);
    }
}

if (!function_exists('num_sqrt')) {
    function num_sqrt(float|int|string $num, ?int $scale = null): string
    {
        return bcsqrt(num_std($num), $scale);
    }
}

if (!function_exists('num_std')) {
    function num_std(float|int|string $num, ?int $scale = null): string
    {
        $num = (string)$num;
        $scale = $scale ?: BC_DEFAULT_SCALE;
        return preg_match('/^[+-]?\d*(\.\d*)?$/', $num) === 1
            ? $num
            : sprintf("%.{$scale}F", $num); // TODO: Fix that $num will be rounded-up
    }
}

if (!function_exists('num_sub')) {
    function num_sub(float|int|string $num1, float|int|string $num2, ?int $scale = null): string
    {
        return bcsub(num_std($num1), num_std($num2), $scale);
    }
}

if (!function_exists('num_trim')) {
    function num_trim(float|int|string $num1, ?int $scale = null): string
    {
        return str_contains(num_std($num1, $scale), '.')
            ? preg_replace('/\.?0+$/', '', num_std($num1, $scale))
            : $num1;
    }
}

if (!function_exists('number_formatter')) {
    function number_formatter(): NumberFormatter
    {
        return Client::numberFormatter();
    }
}

if (!function_exists('out')) {
    function out(...$vars): void
    {
        foreach ($vars as $v) {
            VarDumper::dump($v);
        }
    }
}

if (!function_exists('readable_filesize')) {
    function readable_filesize(float|int $size, string $unit = 'byte'): array
    {
        $units = ['byte', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $maxUnitIndex = count($units) - 1;
        $minUnitIndex = 0;
        if (($index = array_search($unit, $units)) === false) {
            $index = $minUnitIndex;
        }
        if (num_gte($size, 1024)) {
            while (num_gte($size, 1024) && $index < $maxUnitIndex) {
                ++$index;
                $size /= 1024;
            }
        }
        elseif (num_lt($size, 1)) {
            while (num_lt($size, 1) && $index > $minUnitIndex) {
                --$index;
                $size *= 1024;
            }
        }
        $unit = $units[$index];
        return [$size, $unit];
    }
}

if (!function_exists('rtrim_more')) {
    function rtrim_more(string $string, string $characters = ''): string
    {
        return rtrim($string, " \t\n\r\0\x0B" . $characters);
    }
}

if (!function_exists('safe_unserialize')) {
    function safe_unserialize(string $data, bool|array $allowedClasses = true, array $options = []): mixed
    {
        return unserialize($data, [
                'allowed_classes' => $allowedClasses,
            ] + $options);
    }
}

if (!function_exists('snaky_filled_array')) {
    function snaky_filled_array(array $array, array $default = null, $nullable = false): array
    {
        return filled_array($array, $default, $nullable, static fn($key) => Str::snake($key));
    }
}

if (!function_exists('stringable')) {
    function stringable(mixed $value): bool
    {
        return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
    }
}

if (!function_exists('take')) {
    /**
     * @template TValue
     * @param TValue $value
     * @param callable|null $callback
     * @return TValue
     */
    function take(mixed $value, callable $callback = null)
    {
        if (!is_null($callback)) {
            $callback($value);
        }

        return $value;
    }
}

if (!function_exists('trim_more')) {
    function trim_more(string $string, string $characters = ''): string
    {
        return trim($string, " \t\n\r\0\x0B" . $characters);
    }
}

if (!function_exists('uri')) {
    /**
     * @throws UrlGenerationException
     */
    function uri(string $uri, mixed $parameters = [], bool $absolute = true): string
    {
        if ($isAbsolute = (preg_match('/^https?:\/\//', $uri) === 1)) {
            $absolute = false;
        }
        return with(
            url()->toRoute(new Route('GET', $uri, fn() => true), $parameters, $absolute),
            static fn($url) => $isAbsolute ? substr($url, 1) : $url
        );
    }
}

if (!function_exists('with_debug')) {
    function with_debug(Closure $callback, mixed ...$args): mixed
    {
        $origin = config('app.debug');
        config(['app.debug' => true]);
        $called = value($callback, ...$args);
        config(['app.debug' => $origin]);
        return $called;
    }
}

if (!function_exists('with_unlimited_execution_time')) {
    function with_unlimited_execution_time(Closure $callback, mixed ...$args): mixed
    {
        $origin = (int)ini_get('max_execution_time');
        set_time_limit(0);
        $called = value($callback, ...$args);
        set_time_limit($origin);
        return $called;
    }
}

if (!function_exists('with_unlimited_execution_time_if')) {
    function with_unlimited_execution_time_if(bool $condition, Closure $callback, mixed ...$args): mixed
    {
        return $condition ? with_unlimited_execution_time($callback, ...$args) : value($callback, ...$args);
    }
}

if (!function_exists('with_unlimited_execution_time_unless')) {
    function with_unlimited_execution_time_unless(bool $condition, Closure $callback, mixed ...$args): mixed
    {
        return with_unlimited_execution_time_if(!$condition, $callback, ...$args);
    }
}

if (!function_exists('with_unlimited_memory_usage')) {
    function with_unlimited_memory_usage(Closure $callback, mixed ...$args): mixed
    {
        $origin = ini_get('memory_limit');
        ini_set('memory_limit', '-1');
        $called = value($callback, ...$args);
        if (memory_get_usage() < from_ini_filesize($origin)) {
            ini_set('memory_limit', $origin);
        }
        return $called;
    }
}

if (!function_exists('with_unlimited_memory_usage_if')) {
    function with_unlimited_memory_usage_if(bool $condition, Closure $callback, mixed ...$args): mixed
    {
        return $condition ? with_unlimited_memory_usage($callback, ...$args) : value($callback, ...$args);
    }
}

if (!function_exists('with_unlimited_memory_usage_unless')) {
    function with_unlimited_memory_usage_unless(bool $condition, Closure $callback, mixed ...$args): mixed
    {
        return with_unlimited_memory_usage_if(!$condition, $callback, ...$args);
    }
}
