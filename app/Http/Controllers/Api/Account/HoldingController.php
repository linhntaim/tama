<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Resources\CurrentHoldingResource;
use App\Models\HoldingAssetProvider;
use App\Models\HoldingProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Http\Controllers\ModelApiController;
use App\Support\Http\Resources\ResourceTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * @method HoldingProvider modelProvider()
 */
class HoldingController extends ModelApiController
{
    use ResourceTransformer;

    protected string $modelProviderClass = HoldingProvider::class;

    protected string $modelResourceClass = CurrentHoldingResource::class;

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function current(Request $request): JsonResponse
    {
        return $this->responseModel(
            $request,
            $this->modelProvider()->firstOrCreateWithAttributes(['user_id' => $request->user()->id]),
            $this->modelResourceClass
        );
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function save(Request $request): JsonResponse
    {
        $this->modelProvider()->firstOrCreateWithAttributes(['user_id' => $request->user()->id]);

        $this->validate($request, [
            'initial' => 'sometimes|numeric',
            'assets' => 'sometimes|array',
            'assets.*.exchange' => 'required_with:assets|string|max:255',
            'assets.*.symbol' => 'required_with:assets|string|max:255',
            'assets.*.amount' => 'required_with:assets|numeric',
        ]);

        return $this->responseModel(
            $request,
            $this->modelProvider()->update(
                is_null($initial = $request->input('initial')) ? null : (float)$initial,
                is_null($assets = $request->input('assets')) ? null : array_map(function ($asset) {
                    $asset['amount'] = (float)$asset['amount'];
                    return $asset;
                }, $assets)
            ),
            $this->modelResourceClass
        );
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     * @throws ValidationException
     */
    public function assetStore(Request $request): JsonResponse
    {
        $this->validate($request, [
            'exchange' => 'required|string|max:255',
            'symbol' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        return $this->responseModel(
            $request,
            (new HoldingAssetProvider())->add(
                $request->user(),
                $request->input('exchange'),
                $request->input('symbol'),
                (float)$request->input('amount'),
            )
        );
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     * @throws ValidationException
     */
    public function assetUpdate(Request $request, $id): JsonResponse
    {
        ($holdingAssetProvider = new HoldingAssetProvider())->model($id);
        if (!$holdingAssetProvider->belongsTo($request->user())) {
            $this->abort404();
        }

        $this->validate($request, [
            'amount' => 'required|numeric',
        ]);

        return $this->responseModel(
            $request,
            $holdingAssetProvider->updateAmount((float)$request->input('amount'))
        );
    }
}
