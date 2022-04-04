<?php

use App\Http\Controllers\Api\EncryptController;
use App\Http\Controllers\Api\WelcomeController;
use Illuminate\Http\Request;
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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('encrypt', [EncryptController::class, 'encrypt']);
Route::post('decrypt', [EncryptController::class, 'decrypt']);
Route::any('{path?}', [WelcomeController::class, 'index'])
    ->where('path', '.*');
