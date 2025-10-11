<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\PostFavoriteController;
use App\Http\Controllers\PostPurchaseController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\VipController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'admin'])->name('dashboard');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
});

// User favorites
Route::get('/favorites', [PostFavoriteController::class, 'index'])->middleware('auth')->name('favorites');

// Community routes
Route::prefix('community')->name('community.')->group(function () {
    Route::get('/', [CommunityController::class, 'index'])->name('index');
    Route::get('/posts', [CommunityController::class, 'posts'])->name('posts');
    Route::get('/creators', [CommunityController::class, 'creators'])->name('creators');
    Route::get('/leaderboard', [CommunityController::class, 'leaderboard'])->name('leaderboard');
});

// Posts routes
Route::prefix('posts')->name('posts.')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::get('/create', [PostController::class, 'create'])->name('create');
    Route::post('/', [PostController::class, 'store'])->name('store');
    Route::get('/{slug}', [PostController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [PostController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PostController::class, 'update'])->name('update');
    Route::delete('/{id}', [PostController::class, 'destroy'])->name('destroy');

    // Like functionality
    Route::post('/{post}/like', [PostLikeController::class, 'toggle'])->name('like');
    Route::get('/{post}/like-status', [PostLikeController::class, 'status'])->name('like.status');

    // Favorite functionality
    Route::post('/{post}/favorite', [PostFavoriteController::class, 'toggle'])->name('favorite');
    Route::get('/{post}/favorite-status', [PostFavoriteController::class, 'status'])->name('favorite.status');

    // Purchase functionality
    Route::post('/{post}/purchase', [PostPurchaseController::class, 'purchase'])->middleware('auth')->name('purchase');
});

// Creator routes
Route::prefix('creators')->name('creator.')->group(function () {
    Route::get('/{id}', [CreatorController::class, 'show'])->name('show');

    Route::middleware('auth')->group(function () {
        // Follow functionality
        Route::post('/{id}/follow', [CreatorController::class, 'toggleFollow'])->name('follow');

        Route::get('/profile/me', [CreatorController::class, 'profile'])->name('profile');
        Route::get('/apply', [CreatorController::class, 'apply'])->name('apply');
        Route::post('/apply', [CreatorController::class, 'storeApplication'])->name('apply.store');
        Route::get('/profile/edit', [CreatorController::class, 'edit'])->name('edit');
        Route::put('/profile', [CreatorController::class, 'update'])->name('update');
    });
});

// VIP routes
Route::prefix('vip')->name('vip.')->middleware('auth')->group(function () {
    Route::get('/', [VipController::class, 'index'])->name('index');
    Route::get('/subscriptions', [VipController::class, 'mySubscriptions'])->name('subscriptions');
    Route::get('/{slug}', [VipController::class, 'show'])->name('show');
    Route::post('/{slug}/subscribe', [VipController::class, 'subscribe'])->name('subscribe');
    Route::delete('/subscriptions/{id}/cancel', [VipController::class, 'cancel'])->name('cancel');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
