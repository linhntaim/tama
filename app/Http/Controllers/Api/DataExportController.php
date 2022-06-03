<?php

namespace App\Http\Controllers\Api;

use App\Models\DataExport;
use App\Models\DataExportProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Http\Controllers\ModelApiController;
use Illuminate\Http\Request;

/**
 * @method DataExportProvider modelProvider()
 */
class DataExportController extends ModelApiController
{
    protected string $modelProviderClass = DataExportProvider::class;

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function show(Request $request, $id)
    {
        if ($request->has('_file')) {
            return $this->showFile($request, $id);
        }
        if ($request->has('_download')) {
            return $this->showDownload($request, $id);
        }
        return parent::show($request, $id);
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function showFile(Request $request, $id)
    {
        return with($this->modelProvider()->model($id), function (DataExport $dataExport) {
            // TODO: Permission by {$dataExport->name}
            return $dataExport->file->responseFile();
        });
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function showDownload(Request $request, $id)
    {
        return with($this->modelProvider()->model($id), function (DataExport $dataExport) {
            // TODO: Permission by {$dataExport->name}
            return $dataExport->file->responseDownload();
        });
    }
}
