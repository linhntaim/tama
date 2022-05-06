<?php

namespace App\Support\Console\Commands;

use App\Models\File;
use App\Models\FileProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Imports\BatchModelCsvImport;
use App\Support\Imports\Import;

abstract class ImportCommand extends Command
{
    public $signature = '{path} {--per-write=1000} {--delete}';

    protected function path(): string
    {
        return $this->argument('path');
    }

    protected function perWrite(): int
    {
        return (int)($this->option('per-write') ?? 1000);
    }

    protected function importArguments(): array
    {
        return [];
    }

    protected abstract function importClass(): string;

    protected function import(): Import
    {
        return modify($this->importClass(), function ($class) {
            return modify(new $class(...$this->importArguments()), function (Import $import) {
                if ($import instanceof BatchModelCsvImport) {
                    $import->perWrite($this->perWrite());
                }
                return $import;
            });
        });
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function handling(): int
    {
        $this->warn('Import started.');
        $file = (new FileProvider())->createWithFiler(Filer::from($this->path()));
        $fileCloned = false;
        if (!($filer = Filer::from($file))->internal()) {
            $file = (new FileProvider())->createWithFiler($filer->copyToLocal());
            $fileCloned = true;
        }
        $import = $this->import();
        while (!$import->chunkEnded()) {
            $import($file);
        }
        if ($fileCloned) {
            (new FileProvider())->withModel($file)->delete();
        }
        $this->line(sprintf('<info>Imported:</info> %d.', $import->count()));
        return $this->exitSuccess();
    }
}
