<?php

namespace App\Support\Http\Controllers;

use App\Jobs\DataExportJob;
use App\Jobs\DataImportJob;
use App\Jobs\QueueableDataExportJob;
use App\Jobs\QueueableDataImportJob;
use App\Models\DataExport;
use App\Models\DataExportProvider;
use App\Models\DataImport;
use App\Models\DataImportProvider;
use App\Models\File;
use App\Models\FileProvider;
use App\Support\Database\DatabaseTransaction;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Exports\Export;
use App\Support\Exports\ModelCsvExport;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Http\Request;
use App\Support\Http\Resources\ModelResource;
use App\Support\Imports\Import;
use App\Support\Models\ModelProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

trait HasModelApi
{
    use DatabaseTransaction;

    private ModelProvider $modelProvider;

    protected string $modelProviderClass;

    protected string $modelResourceClass = ModelResource::class;

    protected string $sortBy = 'id';

    protected bool $sortAscending = true;

    protected function modelProviderClass(): string
    {
        return $this->modelProviderClass;
    }

    protected function modelProvider(): ModelProvider
    {
        return $this->modelProvider ?? with($this->modelProviderClass(), function ($class) {
                return $this->modelProvider = new $class;
            });
    }

    #region Index
    protected function conditionParams(Request $request): array
    {
        return [];
    }

    protected function defaultConditionParams(Request $request): array
    {
        return [];
    }

    protected function indexConditions(Request $request): array
    {
        $conditions = [];
        foreach ($this->conditionParams($request) as $key => $param) {
            if (is_int($key)) {
                if ($request->if($param, $input, true)) {
                    $conditions[$param] = $input;
                }
                continue;
            }

            if ($request->if($key, $input, true)) {
                if (is_string($param)) {
                    $conditions[$param] = $input;
                }
                elseif (is_callable($param)) {
                    $conditions[$key] = $param($input, $request);
                }
                elseif (is_array($param)) {
                    $found0 = false;
                    $name = $key;
                    if (isset($param['name'])) {
                        $name = $param['name'];
                    }
                    elseif (isset($param[0]) && is_string($param[0])) {
                        $name = $param[0];
                        $found0 = true;
                    }

                    $transform = null;
                    if (isset($param['transform'])) {
                        $transform = $param['transform'];
                    }
                    elseif (isset($param[1]) && is_callable($param[1])) {
                        $transform = $param[1];
                    }
                    elseif (!$found0 && isset($param[0]) && is_callable($param[0])) {
                        $transform = $param[0];
                    }

                    $conditions[$name] = is_callable($transform) ? $transform($input, $request) : $input;
                }
                continue;
            }

            if (is_array($param)) {
                $found0 = false;
                $name = $key;
                if (isset($param['name'])) {
                    $name = $param['name'];
                }
                elseif (isset($param[0]) && is_string($param[0])) {
                    $name = $param[0];
                    $found0 = true;
                }

                $found1 = false;
                if (!isset($param['transform'])) {
                    if (isset($param[1]) && is_callable($param[1])) {
                        $found1 = true;
                    }
                    elseif (!$found0 && isset($param[0]) && is_callable($param[0])) {
                        $found0 = true;
                    }
                }

                $default = null;
                if (isset($param['default'])) {
                    $default = $param['default'];
                }
                elseif (isset($param[2])) {
                    $default = $param[2];
                }
                elseif (!$found1 && isset($param[1])) {
                    $default = $param[1];
                }
                elseif (!$found0 && isset($param[0])) {
                    $default = $param[0];
                }

                if (!is_null($default)) {
                    $conditions[$name] = is_callable($default) ? $default($request) : $default;
                }
            }
        }
        foreach ($this->defaultConditionParams($request) as $key => $param) {
            if (is_int($key)) {
                $conditions[$param] = 1;
            }
            else {
                $conditions[$key] = $param;
            }
        }
        return array_filter($conditions);
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function indexExecute(Request $request)
    {
        return $this->modelProvider()
            ->sort(
                $request->sortBy($this->sortBy),
                $request->sortAscending($this->sortAscending)
            )
            ->pagination(
                $this->indexConditions($request),
                $request->perPage()
            );
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function index(Request $request)
    {
        if ($request->has('_export')) {
            return $this->export($request);
        }
        if ($request->has('_import')) {
            return $this->showImportSampler($request);
        }
        return $this->indexResponse($request, $this->indexExecute($request));
    }

    protected function indexResponse(Request $request, $models)
    {
        return $this->responseModel($request, $models, $this->modelResourceClass);
    }
    #endregion

    #region Export
    protected function exporterClass(Request $request): ?string
    {
        return null;
    }

    protected function exporter(Request $request): Export
    {
        if (is_null($exportClass = $this->exporterClass($request))) {
            abort(404);
        }
        return with(new $exportClass, function (Export $export) use ($request) {
            if ($export instanceof ModelCsvExport) {
                $export
                    ->sort(
                        $request->sortBy($this->sortBy),
                        $request->sortAscending($this->sortAscending)
                    )
                    ->conditions($this->indexConditions($request));
            }
            return $export;
        });
    }

    protected function exportJob(Request $request): string
    {
        return $request->has('queued') ? QueueableDataExportJob::class : DataExportJob::class;
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function exportExecute(Request $request): DataExport
    {
        return (new DataExportProvider())->createWithExport(
            $this->exporter($request),
            $this->exportJob($request),
        );
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function export(Request $request)
    {
        return $this->exportResponse($request, $this->exportExecute($request));
    }

    protected function exportResponse(Request $request, DataExport $model)
    {
        return $this->responseModel($request, $model);
    }
    #endregion

    #region Import
    protected function importerClass(Request $request): ?string
    {
        return null;
    }

    protected function importSampler(Request $request): ?Export
    {
        if (is_null($importClass = $this->importerClass($request))) {
            abort(404);
        }
        return $importClass::sample();
    }

    protected function showImportSampler(Request $request)
    {
        return $this->responseExport($this->importSampler($request));
    }

    protected function importer(Request $request): Import
    {
        if (is_null($importClass = $this->importerClass($request))) {
            abort(404);
        }
        return new $importClass;
    }

    protected function importJob(Request $request): string
    {
        return $request->has('queued') ? QueueableDataImportJob::class : DataImportJob::class;
    }

    protected function importFileInputKey(Request $request): string
    {
        return 'file';
    }

    protected function importFileInput(Request $request): UploadedFile
    {
        return $request->file($this->importFileInputKey($request));
    }

    protected function importFiler(Request $request): Filer
    {
        return Filer::from($this->importFileInput($request));
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function importFile(Request $request): File
    {
        return (new FileProvider())
            ->enablePublish($request->has('queued'))
            ->createWithFiler($this->importFiler($request));
    }

    protected function importRules(Request $request): array
    {
        return [
            'file' => 'required|file|mimetypes:text/plain,text/csv',
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function importValidate(Request $request)
    {
        $this->validate($request, $this->importRules($request));
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function importExecute(Request $request): DataImport
    {
        return (new DataImportProvider())->createWithImport(
            $this->importFile($request),
            $this->importer($request),
            $this->importJob($request),
        );
    }

    /**
     * @throws ValidationException
     * @throws DatabaseException
     * @throws Exception
     */
    protected function import(Request $request)
    {
        $this->importValidate($request);
        return $this->importResponse($request, $this->importExecute($request));
    }

    protected function importResponse(Request $request, DataImport $model)
    {
        return $this->responseModel($request, $model);
    }
    #endregion

    #region Store
    protected function storeRules(Request $request): array
    {
        return [];
    }

    /**
     * @throws ValidationException
     */
    protected function storeValidate(Request $request)
    {
        $this->validate($request, $this->storeRules($request));
    }

    protected function storeExecute(Request $request)
    {
        return null;
    }

    /**
     * @throws ValidationException
     * @throws DatabaseException
     * @throws Exception
     */
    public function store(Request $request)
    {
        if ($request->has('_import')) {
            return $this->import($request);
        }

        $this->storeValidate($request);

        $this->transactionStart();
        return $this->storeResponse($request, $this->storeExecute($request));
    }

    protected function storeResponse(Request $request, $model)
    {
        $this->transactionComplete();
        return $this->responseModel($request, $model, $this->modelResourceClass);
    }
    #endregion

    #region Show
    protected function showExecute(Request $request, $id)
    {
        return $this->modelProvider()->model($id);
    }

    public function show(Request $request, $id)
    {
        return $this->showResponse($request, $this->showExecute($request, $id));
    }

    protected function showResponse(Request $request, $model)
    {
        return $this->responseModel($request, $model, $this->modelResourceClass);
    }
    #endregion

    #region Update
    protected function updateRules(Request $request): array
    {
        return [];
    }

    /**
     * @throws ValidationException
     */
    protected function updateValidate(Request $request)
    {
        $this->validate($request, $this->updateRules($request));
    }

    protected function updateExecute(Request $request)
    {
        return null;
    }

    /**
     * @throws ValidationException
     * @throws DatabaseException
     * @throws Exception
     */
    public function update(Request $request, $id)
    {
        if ($request->has('_delete')) {
            return $this->destroy($request, $id);
        }

        $this->modelProvider()->model($id);

        $this->updateValidate($request);

        $this->transactionStart();
        return $this->updateResponse($request, $this->updateExecute($request));
    }

    protected function updateResponse(Request $request, $model)
    {
        $this->transactionComplete();
        return $this->responseModel($request, $model, $this->modelResourceClass);
    }
    #endregion

    #region Show
    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function destroyExecute(Request $request): bool
    {
        return $this->modelProvider()->delete();
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function destroy(Request $request, $id)
    {
        $this->modelProvider()->model($id);
        $this->transactionStart();
        return $this->destroyResponse($request, $this->destroyExecute($request));
    }

    protected function destroyResponse(Request $request, bool $destroyed)
    {
        $this->transactionComplete();
        return $destroyed
            ? $this->responseSuccess($request)
            : $this->responseFail($request);
    }
    #endregion
}
