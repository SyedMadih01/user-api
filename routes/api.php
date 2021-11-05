<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/invite-user-to-reg', [App\Http\Controllers\Admin\UserController::class, 'send_user_reg_link']);
Route::post('/store-user',[App\Http\Controllers\Admin\UserController::class,'store_user']);
Route::get('/verify-email/{email}/{pin}', [App\Http\Controllers\Admin\UserController::class, 'verify_email']);

Route::post('/user/login', [\App\Http\Controllers\UserController::class, 'login']);
Route::group(['middleware' => ['cors', 'json.response', 'auth:api']], function () {
    Route::post('/user/update-profile',[\App\Http\Controllers\UserController::class,'update_profile']);
});
