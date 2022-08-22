<?php

use App\Http\Controllers\Api\Account\AccountController;
use App\Http\Controllers\Api\Account\HoldingAssetController as AccountHoldingAssetController;
use App\Http\Controllers\Api\Account\HoldingController as AccountHoldingController;
use App\Http\Controllers\Api\Auth\NewPasswordController;
use App\Http\Controllers\Api\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\Auth\RegisteredUserController;
use App\Http\Controllers\Api\Auth\Sanctum\AuthenticatedTokenController as SanctumAuthenticatedTokenController;
use App\Http\Controllers\Api\DataExportController;
use App\Http\Controllers\Api\EncryptController;
use App\Http\Controllers\Api\ExchangeController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\PrerequisiteController;
use App\Http\Controllers\Api\SwingTradeController;
use App\Http\Controllers\Api\Trial\EventController as TrialEventController;
use App\Http\Controllers\Api\Trial\FileController as TrialFileController;
use App\Http\Controllers\Api\Trial\JobController as TrialJobController;
use App\Http\Controllers\Api\Trial\UserController as TrialUserController;
use App\Http\Controllers\Api\WelcomeController;
use App\Trading\Http\Controllers\Api\Integration\Telegram\BotController as TelegramBotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('encrypt', [EncryptController::class, 'encrypt']);
Route::post('decrypt', [EncryptController::class, 'decrypt']);
Route::get('prerequisite', [PrerequisiteController::class, 'index']);
Route::get('file/{id}', [FileController::class, 'show'])->name('file.show');
Route::get('data-export/{id}', [DataExportController::class, 'show'])->name('data-export.show');

//
// Route::group([
//     'prefix' => 'trial',
// ], function () {
//     Route::post('job', [TrialJobController::class, 'store']);
//     Route::post('event', [TrialEventController::class, 'store']);
//     Route::group([
//         'prefix' => 'file',
//     ], function () {
//         Route::post('/', [TrialFileController::class, 'store']);
//         Route::get('{id}', [TrialFileController::class, 'show']);
//     });
//     Route::group([
//         'prefix' => 'user',
//     ], function () {
//         Route::get('/', [TrialUserController::class, 'index']);
//         Route::post('/', [TrialUserController::class, 'store']);
//         Route::get('{id}', [TrialUserController::class, 'show']);
//         Route::post('{id}', [TrialUserController::class, 'update']);
//         Route::delete('{id}', [TrialUserController::class, 'destroy']);
//     });
// });

Route::group([
    'prefix' => 'auth',
], function () {
    Route::post('sanctum/login', [SanctumAuthenticatedTokenController::class, 'store']);
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);
    Route::post('reset-password', [NewPasswordController::class, 'store']);
});

Route::group([
    'middleware' => 'auth:sanctum',
], function () {
    Route::group([
        'prefix' => 'auth',
    ], function () {
        Route::post('sanctum/logout', [SanctumAuthenticatedTokenController::class, 'destroy']);
    });

    Route::group([
        'prefix' => 'account',
    ], function () {
        Route::get('current', [AccountController::class, 'current']);

        //
        Route::group([
            'prefix' => 'holding',
        ], function () {
            Route::group([
                'prefix' => 'asset',
            ], function () {
                Route::post('/', [AccountHoldingAssetController::class, 'store']);
                Route::post('{id}', [AccountHoldingAssetController::class, 'update']);
                Route::delete('{id}', [AccountHoldingAssetController::class, 'destroy']);
            });

            Route::get('current', [AccountHoldingController::class, 'current']);
            Route::post('current', [AccountHoldingController::class, 'save']);
        });
    });
});

Route::group([
    'prefix' => 'exchange',
], function () {
    Route::get('/', [ExchangeController::class, 'index']);
    Route::group([
        'prefix' => '{exchange}',
    ], function () {
        Route::get('ticker', [ExchangeController::class, 'tickerIndex']);
        Route::get('interval', [ExchangeController::class, 'intervalIndex']);
        Route::get('symbol/{symbol}', [ExchangeController::class, 'symbolShow']);
    });
});
Route::get('swing-trade/{exchange}/{indicator}', [SwingTradeController::class, 'show']);

Route::group([
    'prefix' => 'integration',
], function () {
    Route::group([
        'prefix' => 'telegram',
    ], function () {
        Route::post('bot', [TelegramBotController::class, 'store']);
    });
});

Route::any('{path?}', [WelcomeController::class, 'index'])
    ->where('path', '.*');
