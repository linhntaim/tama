<?php

namespace App\Support\Console\Commands;

use App\Models\FileProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Exports\Export;
use App\Support\Exports\ModelCsvExport;
use App\Support\Http\Resources\ModelResourceTransformer;

abstract class ExportCommand extends Command
{
    use ModelResourceTransformer;

    public $signature = '{--per-read=1000}';

    protected function perRead(): int
    {
        return (int)($this->option('per-read') ?? 1000);
    }

    protected function exportArguments(): array
    {
        return [];
    }

    protected abstract function exportClass(): string;

    protected function export(): Export
    {
        return modify($this->exportClass(), function ($class) {
            return modify(new $class(...$this->exportArguments()), function (Export $export) {
                if ($export instanceof ModelCsvExport) {
                    $export->perRead($this->perRead());
                }
                return $export;
            });
        });
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function handling(): int
    {
        $this->warn('Export started.');
        $export = $this->export();
        $file = (new FileProvider())->createWithFiler($export());
        while (!$export->chunkEnded()) {
            $file = (new FileProvider())
                ->withModel($file)
                ->updateWithFiler($export($file));
        }
        $this->line(sprintf('<info>Exported:</info> %d.', $export->count()));
        $this->info('File:');
        print_r($this->modelResourceTransform($file));
        return $this->exitSuccess();
    }
}
