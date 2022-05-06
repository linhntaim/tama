<?php

namespace App\Support\Http\Controllers;

use App\Support\Database\DatabaseTransaction;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Http\Request;
use App\Support\Http\Resources\ModelResource;
use App\Support\Models\Model;
use App\Support\Models\ModelProvider;
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

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function index(Request $request)
    {
        return $this->indexResponse($request, $this->indexExecute($request));
    }

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
            ->sort($request->sortBy($this->sortBy), $request->sortAscending($this->sortAscending))
            ->pagination(
                $this->indexConditions($request),
                $request->perPage()
            );
    }

    protected function indexResponse(Request $request, $models)
    {
        return $this->responseModel($request, $models, $this->modelResourceClass);
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
     */
    public function store(Request $request)
    {
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
}
