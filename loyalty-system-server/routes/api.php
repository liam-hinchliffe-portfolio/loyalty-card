<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Models\User;
use \App\Http\Controllers\UserController;

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


Route::put('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/user', [UserController::class, 'authUser']);
    Route::get('/logout', [UserController::class, 'logout']);
    Route::put('/user/employee/register', [UserController::class, 'registerEmployee']);
    Route::get('/users/search/{searchQuery}', [UserController::class, 'search']);
    Route::post('/user/{userId}/addPoints', [UserController::class, 'addPoints']);
    Route::post('/user/{userId}/removePoints', [UserController::class, 'removePoints']);
    Route::delete('/user/{id}', [UserController::class, 'softDelete']);
    Route::post('/user/{userId}/restore', [UserController::class, 'restore']);
});

Route::get('/user/{id}', [UserController::class, 'get']);
Route::get('/users', [UserController::class, 'index']);
