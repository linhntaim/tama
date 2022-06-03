<?php

namespace App\Support\Log;

use App\Support\Console\RunningCommand;
use App\Support\Exceptions\ShellException;
use App\Support\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Monolog\Formatter\LineFormatter as BaseLineFormatter;
use Throwable;

class LineFormatter extends BaseLineFormatter
{
    use Requests;

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
        $normalized[] = trim($this->advancedRequest());
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
        if ($e instanceof ShellException && ($output = $e->getOutput())) {
            $normalized[] = '[Output]';
            $normalized[] = $output;
        }
        $normalized[] = '[Trace]';
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
                $order = Str::padLeft($i, $padLength, '0');
                if (isset($trace['file'])) {
                    $normalized[] = sprintf(
                        '#%s [%s:%s]',
                        $order,
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
                            $order,
                            $trace['class'] ?? '',
                            $trace['type'] ?? '',
                            $trace['function'] ?? '',
                            implode(', ', array_map(fn($arg) => describe_var($arg), $trace['args'] ?? []))
                        );
                    }
                    elseif (isset($trace['text'])) {
                        $normalized[] = sprintf(
                            '#%s %s',
                            $order,
                            $trace['text'] ?? ''
                        );
                    }
                    else {
                        $normalized[] = sprintf(
                            '#%s %s',
                            $order,
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
