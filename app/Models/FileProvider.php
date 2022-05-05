<?php

namespace App\Models;

use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Models\Model;
use App\Support\Models\ModelProvider;

/**
 * @property File|null $model
 * @method File model(Model|callable|int|string $model = null)
 * @method File createWithAttributes(array $attributes = [])
 * @method File updateWithAttributes(array $attributes = [])
 */
class FileProvider extends ModelProvider
{
    public function modelClass(): string
    {
        return File::class;
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function createWithFiler(Filer $filer, ?string $title = null): File
    {
        return $this->createWithAttributes([
            'name' => $name = $filer->getName(),
            'title' => $title ?? pathinfo($name, PATHINFO_FILENAME),
            'mime' => $filer->getMimeType(),
            'extension' => $filer->getExtension(),
            'size' => (string)$filer->getSize(),
            'storage' => $filer->getStorage(),
            'options' => $filer->getOptions(),
            'file' => $filer->getFile(),
        ]);
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function updateWithFiler(Filer $filer, ?string $title = null): File
    {
        return $this->updateWithAttributes([
                'name' => $filer->getName(),
                'mime' => $filer->getMimeType(),
                'extension' => $filer->getExtension(),
                'size' => (string)$filer->getSize(),
                'storage' => $filer->getStorage(),
                'options' => $filer->getOptions(),
                'file' => $filer->getFile(),
            ] + array_filter([
                'title' => $title,
            ]));
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function updateTitle(string $title): File
    {
        return $this->updateWithAttributes([
            'title' => $title,
        ]);
    }

    public function delete(): bool
    {
        Filer::from($this->model)->delete();
        return parent::delete();
    }

    public function deleteAll(array $conditions = []): bool
    {
        $all = $this->all($conditions);
        parent::deleteAll($conditions);
        foreach ($all as $model) {
            Filer::from($model)->delete();
        }
        return true;
    }
}
