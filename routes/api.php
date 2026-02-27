<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Instagram Clone API',
        'version' => '1.0',
        'endpoints' => [
            'auth' => [
                'POST /api/auth/register' => 'Register new user',
                'POST /api/auth/login' => 'Login',
                'POST /api/auth/logout' => 'Logout (auth required)',
            ],
        ],
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [App\Http\Controllers\Api\ProfileController::class, 'show']);
    Route::put('/profile', [App\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::post('/profile/avatar', [App\Http\Controllers\Api\ProfileController::class, 'changeAvatar']);
});

