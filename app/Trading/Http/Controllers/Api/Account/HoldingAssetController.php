<?php

namespace App\Trading\Http\Controllers\Api\Account;

use App\Support\Http\Controllers\ModelApiController;
use App\Support\Http\Resources\Concerns\ResourceTransformer;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Models\HoldingAsset;
use App\Trading\Models\HoldingAssetProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * @method HoldingAssetProvider modelProvider()
 */
class HoldingAssetController extends ModelApiController
{
    use ResourceTransformer;

    protected string $modelProviderClass = HoldingAssetProvider::class;

    protected function storeRules(Request $request): array
    {
        return [
            'exchange' => 'required|string|max:255|in:' . implode(',', Exchanger::available()),
            'symbol' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ];
    }

    protected function storeValidate(Request $request)
    {
        parent::storeValidate($request);

        // Validate symbol
        Exchanger::connector($request->input('exchange'))->symbolPrice($request->input('symbol'));
    }

    protected function storeExecute(Request $request): HoldingAsset
    {
        return $this->modelProvider()->add(
            $request->user(),
            $request->input('exchange'),
            $request->input('symbol'),
            (float)$request->input('amount'),
        );
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->has('_orders')) {
            return $this->updateOrders($request);
        }
        return parent::store($request);
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    protected function updateOrders(Request $request): JsonResponse
    {
        $this->validate($request, [
            'assets' => 'required|array',
            'assets.*.id' => 'required|integer',
            'assets.*.order' => 'required|integer',
        ]);

        $this->transactionStart();
        try {
            foreach ($request->input('assets') as $asset) {
                if (!$this->modelProvider()
                    ->withModel($asset['id'])
                    ->belongsTo($request->user())) {
                    $this->abort404();
                }
                $this->modelProvider()->updateOrder($asset['order']);
            }
            $this->transactionComplete();
        }
        catch (Throwable $exception) {
            $this->transactionAbort();
            throw $exception;
        }
        return $this->responseSuccess($request);
    }

    protected function updateRules(Request $request): array
    {
        return [
            'amount' => 'required|numeric',
        ];
    }

    protected function updateValidate(Request $request)
    {
        if (!$this->modelProvider()->belongsTo($request->user())) {
            $this->abort404();
        }
        parent::updateValidate($request);
    }

    protected function updateExecute(Request $request): HoldingAsset
    {
        return $this->modelProvider()->updateAmount((float)$request->input('amount'));
    }

    protected function destroyValidate(Request $request)
    {
        if (!$this->modelProvider()->belongsTo($request->user())) {
            $this->abort404();
        }
        parent::destroyValidate($request);
    }
}
