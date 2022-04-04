<?php

use App\Http\Controllers\Api\EncryptController;
use App\Http\Controllers\Api\PrerequisiteController;
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
Route::post('encrypt', [EncryptController::class, 'encrypt']);
Route::post('decrypt', [EncryptController::class, 'decrypt']);

//

Route::any('{path?}', [WelcomeController::class, 'index'])
    ->where('path', '.*');
