<?php

namespace App\Support\Console;

use App\Support\Exceptions\ShellException;
use Closure;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class Sheller
{
    protected string $cmd;

    protected int $exitCode = 0;

    protected Process $process;

    protected bool $exceptionOnError = true;

    protected function getProcess(): ?Process
    {
        return $this->process ?? null;
    }

    public function exceptionOnError(bool $exceptionOnError): static
    {
        $this->exceptionOnError = $exceptionOnError;
        return $this;
    }

    /**
     * @throws ShellException
     */
    public function run(string $cmd, Closure $callback = null): int
    {
        $this->cmd = $cmd;
        Log::info(sprintf('Shell [%s] started.', $this->cmd));
        $this->process = Process::fromShellCommandline($this->cmd, base_path(), null, null, null);
        $this->exitCode = $this->process->run($callback);
        if ($this->successful()) {
            Log::info(sprintf('Shell [%s] ended.', $this->cmd));
        }
        else {
            if ($this->exceptionOnError) {
                throw new ShellException($this);
            }
            Log::error(sprintf('Shell [%s] failed (exit code: %d). %s', $this->cmd, $this->exitCode, PHP_EOL . $this->output()));
        }
        return $this->exitCode;
    }

    public function cmd(): string
    {
        return $this->cmd;
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }

    public function successful(): bool
    {
        return $this->exitCode == 0;
    }

    public function output(): ?string
    {
        return (fn($output) => $output ? trim($output) : $output)(
            $this->successful()
                ? $this->getProcess()?->getOutput()
                : ($this->getProcess()?->getErrorOutput() ?: $this->getProcess()?->getOutput())
        );
    }
}