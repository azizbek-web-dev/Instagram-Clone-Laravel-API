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
    Route::get('/profile/posts', [App\Http\Controllers\Api\ProfileController::class, 'posts']);

    Route::get('/posts/feed', [App\Http\Controllers\Api\PostController::class, 'feed']);
    Route::post('/posts', [App\Http\Controllers\Api\PostController::class, 'store']);
    Route::get('/posts/{post}', [App\Http\Controllers\Api\PostController::class, 'show']);
    Route::delete('/posts/{post}', [App\Http\Controllers\Api\PostController::class, 'destroy']);
    Route::post('/posts/{post}/like', [App\Http\Controllers\Api\PostController::class, 'like']);
    Route::delete('/posts/{post}/like', [App\Http\Controllers\Api\PostController::class, 'unlike']);
    Route::post('/posts/{post}/save', [App\Http\Controllers\Api\PostController::class, 'save']);
    Route::delete('/posts/{post}/save', [App\Http\Controllers\Api\PostController::class, 'unsave']);
    Route::post('/posts/{post}/comments', [App\Http\Controllers\Api\PostController::class, 'storeComment']);
    Route::get('/posts/{post}/comments', [App\Http\Controllers\Api\PostController::class, 'getComments']);

    Route::get('/stories', [App\Http\Controllers\Api\StoryController::class, 'index']);
    Route::post('/stories', [App\Http\Controllers\Api\StoryController::class, 'store']);
    Route::get('/stories/{username}', [App\Http\Controllers\Api\StoryController::class, 'show']);
    Route::delete('/stories/{story}', [App\Http\Controllers\Api\StoryController::class, 'destroy']);

    Route::post('/users/{user}/follow', [App\Http\Controllers\Api\FollowController::class, 'follow']);
    Route::delete('/users/{user}/follow', [App\Http\Controllers\Api\FollowController::class, 'unfollow']);
    Route::get('/users/{user}/followers', [App\Http\Controllers\Api\FollowController::class, 'followers']);
    Route::get('/users/{user}/following', [App\Http\Controllers\Api\FollowController::class, 'following']);

    Route::get('/search/users', [App\Http\Controllers\Api\SearchController::class, 'users']);
    Route::get('/search/posts', [App\Http\Controllers\Api\SearchController::class, 'posts']);
});

