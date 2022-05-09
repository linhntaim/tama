<?php

use App\Http\Controllers\Api\DataExportController;
use App\Http\Controllers\Api\EncryptController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\PrerequisiteController;
use App\Http\Controllers\Api\SwingTradeController;
use App\Http\Controllers\Api\Trial\EventController as TrialEventController;
use App\Http\Controllers\Api\Trial\FileController as TrialFileController;
use App\Http\Controllers\Api\Trial\JobController as TrialJobController;
use App\Http\Controllers\Api\Trial\UserController as TrialUserController;
use App\Http\Controllers\Api\WelcomeController;
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
Route::group([
    'prefix' => 'trial',
], function () {
    Route::post('job', [TrialJobController::class, 'store']);
    Route::post('event', [TrialEventController::class, 'store']);
    Route::group([
        'prefix' => 'file',
    ], function () {
        Route::post('/', [TrialFileController::class, 'store']);
        Route::get('{id}', [TrialFileController::class, 'show']);
    });
    Route::group([
        'prefix' => 'user',
    ], function () {
        Route::get('/', [TrialUserController::class, 'index']);
        Route::post('/', [TrialUserController::class, 'store']);
        Route::get('{id}', [TrialUserController::class, 'show']);
        Route::post('{id}', [TrialUserController::class, 'update']);
        Route::delete('{id}', [TrialUserController::class, 'destroy']);
    });
});

Route::get('swing-trade/{exchange}/{indicator}', [SwingTradeController::class, 'show']);

Route::get('ping', [WelcomeController::class, 'ping']);

Route::any('{path?}', [WelcomeController::class, 'index'])
    ->where('path', '.*');
