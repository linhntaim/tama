<?php

namespace App\Http\Controllers\Api;

use App\Models\File;
use App\Models\FileProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Http\Controllers\ModelApiController;
use App\Support\Http\Request;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * @method FileProvider modelProvider()
 */
class FileController extends ModelApiController
{
    protected string $modelProviderClass = FileProvider::class;

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
        return $this->responseFail($request);
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function showFile(Request $request, $id)
    {
        return with($this->modelProvider()->model($id), function (File $file) {
            if ($file->visibility == Filesystem::VISIBILITY_PRIVATE) {
                abort(403, 'Access denied.');
            }
            return $file->responseFile();
        });
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function showDownload(Request $request, $id)
    {
        return with($this->modelProvider()->model($id), function (File $file) {
            if ($file->visibility == Filesystem::VISIBILITY_PRIVATE) {
                abort(403, 'Access denied.');
            }
            return $file->responseDownload();
        });
    }
}
