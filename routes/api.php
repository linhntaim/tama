<?php

use App\Http\Controllers\Api\EncryptController;
use App\Http\Controllers\Api\PrerequisiteController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\Trial\EventController as TrialEventController;
use App\Http\Controllers\Api\Trial\JobController as TrialJobController;
use App\Http\Controllers\Api\Trial\UserController;
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

Route::get('prerequisite', [PrerequisiteController::class, 'index']);
Route::get('file/{id}', [FileController::class, 'show']);
Route::post('encrypt', [EncryptController::class, 'encrypt']);
Route::post('decrypt', [EncryptController::class, 'decrypt']);

//
Route::group([
    'prefix' => 'trial',
], function () {
    Route::get('user', [UserController::class, 'index']);
    Route::post('job', [TrialJobController::class, 'store']);
    Route::post('event', [TrialEventController::class, 'store']);
});

Route::any('{path?}', [WelcomeController::class, 'index'])
    ->where('path', '.*');
