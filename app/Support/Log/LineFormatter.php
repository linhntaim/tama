<?php

namespace App\Support\Log;

use Monolog\Formatter\LineFormatter as BaseLineFormatter;
use Throwable;

class LineFormatter extends BaseLineFormatter
{
    protected function normalizeException(Throwable $e, int $depth = 0): string
    {
        $normalized[] = get_debug_type($e);
        $normalized[] = $e->getMessage();
        $normalized[] = 'Exception trace';
        $traces = $e->getTrace();
        $traces[] = [
            'text' => '{main}',
        ];
        $padLength = strlen(count($traces));
        foreach ($traces as $i => $trace) {
            if (isset($trace['file'])) {
                $normalized[] = sprintf(
                    '#%s [%s:%s]',
                    str($i + 1)->padLeft($padLength, '0'),
                    $trace['file'] ?? '',
                    $trace['line'] ?? ''
                );
                if (isset($trace['function'])) {
                    $normalized[] = sprintf(
                        '%s %s%s%s(%s)',
                        str_repeat(' ', $padLength + 1),
                        $trace['class'] ?? '',
                        $trace['type'] ?? '',
                        $trace['function'] ?? '',
                        implode(', ', array_map(fn($arg) => describe_var($arg), $trace['args'] ?? []))
                    );
                }
                elseif (isset($trace['text'])) {
                    $normalized[] = sprintf(
                        '%s %s',
                        str_repeat(' ', $padLength + 1),
                        $trace['text'] ?? ''
                    );
                }
            }
            else {
                if (isset($trace['function'])) {
                    $normalized[] = sprintf(
                        '#%s %s%s%s(%s)',
                        str($i + 1)->padLeft($padLength, '0'),
                        $trace['class'] ?? '',
                        $trace['type'] ?? '',
                        $trace['function'] ?? '',
                        implode(', ', array_map(fn($arg) => describe_var($arg), $trace['args'] ?? []))
                    );
                }
                elseif (isset($trace['text'])) {
                    $normalized[] = sprintf(
                        '#%s %s',
                        str($i + 1)->padLeft($padLength, '0'),
                        $trace['text'] ?? ''
                    );
                }
                else {
                    $normalized[] = sprintf(
                        '#%s %s',
                        str($i + 1)->padLeft($padLength, '0'),
                        json_encode($trace)
                    );
                }
            }
        }
        $normalized[] = '';
        return implode(PHP_EOL, $normalized);
    }
}