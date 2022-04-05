<?php

/**
 * Base
 */

namespace App\Support\Log;

use App\Support\Console\RunningCommand;
use App\Support\Http\Request;
use Monolog\Formatter\LineFormatter as BaseLineFormatter;
use Throwable;

class LineFormatter extends BaseLineFormatter
{
    public const SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %context.app_id% %message% %context% %extra% %context.request% %context.exception%\n";

    protected function normalize($data, int $depth = 0)
    {
        if ($depth > $this->maxNormalizeDepth) {
            return 'Over ' . $this->maxNormalizeDepth . ' levels deep, aborting normalization';
        }
        if ($data instanceof Request) {
            return $this->normalizeRequest($data, $depth);
        }
        if ($data instanceof RunningCommand) {
            return $this->normalizeCommand($data, $depth);
        }
        return parent::normalize($data, $depth);
    }

    protected function normalizeRequest(Request $request, int $depth = 0): string
    {
        $normalized[] = '';
        $normalized[] = '<Request>';
        $normalized[] = trim($request);
        return implode(PHP_EOL, $normalized);
    }

    protected function normalizeCommand(RunningCommand $runningCommand, int $depth = 0): string
    {
        $normalized[] = '';
        $normalized[] = '<Command>';
        $normalized[] = sprintf('%s: %s %s', $runningCommand->command::class, $runningCommand->command->getName(), trim(strstr($runningCommand->input, ' ')));
        return implode(PHP_EOL, $normalized);
    }

    protected function normalizeException(Throwable $e, int $depth = 0): string
    {
        $normalized[] = '';
        $normalized[] = '<Exception>';
        do {
            $traces = $e->getTrace();
            $traces[] = [
                'text' => '{main}',
            ];
            $padLength = strlen(count($traces) + 1);
            array_unshift($traces, [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'text' => implode(PHP_EOL, [
                    get_debug_type($e) . ':',
                    str_repeat(' ', $padLength + 1) . ' - ' . $e->getMessage(),
                ]),
            ]);
            foreach ($traces as $i => $trace) {
                $order = str($i);
                if (isset($trace['file'])) {
                    $normalized[] = sprintf(
                        '#%s [%s:%s]',
                        $order->padLeft($padLength, '0'),
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
                            $order->padLeft($padLength, '0'),
                            $trace['class'] ?? '',
                            $trace['type'] ?? '',
                            $trace['function'] ?? '',
                            implode(', ', array_map(fn($arg) => describe_var($arg), $trace['args'] ?? []))
                        );
                    }
                    elseif (isset($trace['text'])) {
                        $normalized[] = sprintf(
                            '#%s %s',
                            $order->padLeft($padLength, '0'),
                            $trace['text'] ?? ''
                        );
                    }
                    else {
                        $normalized[] = sprintf(
                            '#%s %s',
                            $order->padLeft($padLength, '0'),
                            json_encode_readable($trace)
                        );
                    }
                }
            }
        }
        while (($e = $e->getPrevious()) && ($normalized[] = str_repeat('-', 50)));
        return implode(PHP_EOL, $normalized);
    }
}