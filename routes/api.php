<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PostLikeController;
use App\Http\Controllers\Api\PostCategoryController;
use App\Http\Controllers\Api\CreatorProfileController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\UserPointsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MediaController;

// Auth routes
Route::post('/auth/token', [AuthController::class, 'token']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// Legacy user endpoint for compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API routes
Route::prefix('v1')->group(function () {
    // Posts
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{id}', [PostController::class, 'show']);
    Route::get('posts/{post}/like-status', [PostLikeController::class, 'status']);

    // Post Categories
    Route::get('categories', [PostCategoryController::class, 'index']);
    Route::get('categories/{slug}', [PostCategoryController::class, 'show']);

    // Creators
    Route::get('creators', [CreatorProfileController::class, 'index']);
    Route::get('creators/{id}', [CreatorProfileController::class, 'show']);

    // Plans
    Route::get('plans', [PlanController::class, 'index']);
    Route::get('plans/{slug}', [PlanController::class, 'show']);

    // Points Leaderboard
    Route::get('leaderboard', [UserPointsController::class, 'leaderboard']);
});

// Protected API routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Posts - authenticated actions
    Route::post('posts', [PostController::class, 'store']);
    Route::put('posts/{id}', [PostController::class, 'update']);
    Route::delete('posts/{id}', [PostController::class, 'destroy']);
    Route::post('posts/{post}/like', [PostLikeController::class, 'toggle']);
    Route::post('posts/{id}/favorite', [PostController::class, 'favorite']);

    // Creator Profiles - authenticated actions
    Route::post('creators', [CreatorProfileController::class, 'store']);
    Route::put('creators/{id}', [CreatorProfileController::class, 'update']);
    Route::delete('creators/{id}', [CreatorProfileController::class, 'destroy']);
    Route::post('creators/{id}/follow', [CreatorProfileController::class, 'follow']);
    Route::get('my-creator-profile', [CreatorProfileController::class, 'myProfile']);

    // User Points
    Route::get('my-points', [UserPointsController::class, 'index']);

    // Media uploads
    Route::post('media/presigned-url', [MediaController::class, 'getPresignedUrl']);
    Route::post('media/confirm-upload/{tempUploadId}', [MediaController::class, 'confirmUpload']);
    Route::post('media/upload-video', [MediaController::class, 'uploadVideo']); // Legacy
    Route::post('media/upload-image', [MediaController::class, 'uploadImage']);
    Route::delete('media/temporary-uploads/{id}', [MediaController::class, 'deleteTemporaryUpload']);
});