<?php

namespace App\Http\Controllers\Api;

use App\Support\Http\Controllers\ModelApiController;
use App\Support\Models\File;
use App\Support\Models\FileProvider;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;

/**
 * @method FileProvider modelProvider()
 */
class FileController extends ModelApiController
{
    protected string $modelProviderClass = FileProvider::class;

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

    protected function showFile(Request $request, $id)
    {
        return with($this->modelProvider()->model($id), function (File $file) {
            if ($file->visibility == Filesystem::VISIBILITY_PRIVATE) {
                abort(403, 'Access denied.');
            }
            return $file->responseFile();
        });
    }

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
