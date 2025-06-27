<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TaskHistoryController;

Route::prefix('v1')->group(function () {
    Route::post('/register', [RegisterController::class, 'store']);
    Route::post('/login', [LoginController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [UserController::class, 'me']);
        Route::put('/me', [UserController::class, 'update']);
        Route::delete('/me', [UserController::class, 'destroy']);
        Route::post('/logout', [LoginController::class, 'logout']);

        Route::apiResource('tasks', TaskController::class);
        Route::get('tasks/{task}/history', [TaskHistoryController::class, 'index']);
    });
});

