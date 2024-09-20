<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'users', 'controller' => UserController::class], function ($router) {
    Route::get('', 'index');
});


Route::group(['prefix' => 'auth', 'controller' => AuthController::class], function ($router) {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::get('profile', 'profile');
    Route::post('update-profile', 'updateProfile');
});
Route::group(['prefix' => 'chats', 'controller' => ChatController::class], function ($router) {
    Route::get('', 'index');
    Route::get('show/{id}', 'show');
    Route::post('/create', 'create');
});



Route::group(['prefix' => 'chat-messages', 'controller' => ChatMessageController::class], function ($router) {
    Route::get('/{chatId}', 'index');
    Route::post('/create-message', 'create');
});
