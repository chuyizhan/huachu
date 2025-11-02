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
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\V1\MediaController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'admin'])->name('dashboard');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::post('users/{user}/consolidate-balances', [App\Http\Controllers\Admin\UserController::class, 'consolidateBalances'])->name('users.consolidate-balances');
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('posts', App\Http\Controllers\Admin\PostController::class);
    Route::resource('plans', App\Http\Controllers\Admin\PlanController::class);
    Route::resource('recharge-packages', App\Http\Controllers\Admin\RechargePackageController::class);
    Route::resource('point-transactions', App\Http\Controllers\Admin\PointTransactionController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('credit-transactions', App\Http\Controllers\Admin\CreditTransactionController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::get('platform-transactions', [App\Http\Controllers\Admin\PlatformTransactionController::class, 'index'])->name('platform-transactions.index');
    Route::get('orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');

    // Creators Management Routes
    Route::resource('creators', App\Http\Controllers\Admin\CreatorController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::post('creators/{id}/verify', [App\Http\Controllers\Admin\CreatorController::class, 'verify'])->name('creators.verify');
    Route::post('creators/{id}/reject', [App\Http\Controllers\Admin\CreatorController::class, 'reject'])->name('creators.reject');
    Route::post('creators/{id}/toggle-featured', [App\Http\Controllers\Admin\CreatorController::class, 'toggleFeatured'])->name('creators.toggle-featured');

    // Creator Subscription Plans Management
    Route::prefix('creators/{creator}/subscription-plans')->name('creator-subscription-plans.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CreatorSubscriptionPlanController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\CreatorSubscriptionPlanController::class, 'store'])->name('store');
    });
    Route::resource('creator-subscription-plans', App\Http\Controllers\Admin\CreatorSubscriptionPlanController::class)->only(['update', 'destroy']);

    // Post Review Routes
    Route::get('post-reviews', [App\Http\Controllers\Admin\PostReviewController::class, 'index'])->name('post-reviews.index');
    Route::get('post-reviews/{id}', [App\Http\Controllers\Admin\PostReviewController::class, 'show'])->name('post-reviews.show');
    Route::post('post-reviews/{id}/approve', [App\Http\Controllers\Admin\PostReviewController::class, 'approve'])->name('post-reviews.approve');
    Route::post('post-reviews/{id}/reject', [App\Http\Controllers\Admin\PostReviewController::class, 'reject'])->name('post-reviews.reject');
    Route::post('post-reviews/{id}/reset', [App\Http\Controllers\Admin\PostReviewController::class, 'resetReview'])->name('post-reviews.reset');
    Route::post('post-reviews/batch-approve', [App\Http\Controllers\Admin\PostReviewController::class, 'batchApprove'])->name('post-reviews.batch-approve');
});

// User favorites
Route::get('/favorites', [PostFavoriteController::class, 'index'])->middleware('auth')->name('favorites');

// Profile route
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth')->name('profile');

// Media upload routes (for Inertia/web auth)
Route::prefix('api/v1/media')->middleware('auth')->group(function () {
    Route::post('/presigned-url', [MediaController::class, 'getPresignedUrl']);
    Route::post('/confirm-upload/{id}', [MediaController::class, 'confirmUpload']);
    Route::delete('/temp-uploads/{id}', [MediaController::class, 'deleteTempUpload']);
});

// Recharge routes
Route::prefix('recharge')->name('recharge.')->middleware('auth')->group(function () {
    Route::get('/', [RechargeController::class, 'index'])->name('index');
    Route::post('/process', [RechargeController::class, 'process'])->name('process');
    Route::get('/success/{order}', [RechargeController::class, 'success'])->name('success');
});

// Comment routes
Route::prefix('comments')->name('comments.')->middleware('auth')->group(function () {
    Route::post('/', [CommentController::class, 'store'])->name('store');
    Route::put('/{comment}', [CommentController::class, 'update'])->name('update');
    Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
});

// Community routes
Route::prefix('community')->name('community.')->group(function () {
    // Route::get('/', [CommunityController::class, 'index'])->name('index');
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
    Route::get('/', [CreatorController::class, 'index'])->name('index');
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

// Creator subscription routes
Route::prefix('subscriptions')->name('subscriptions.')->middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\CreatorSubscriptionController::class, 'mySubscriptions'])->name('index');
    Route::post('/plans/{plan}/subscribe', [App\Http\Controllers\CreatorSubscriptionController::class, 'subscribe'])->name('subscribe');
    Route::post('/{subscription}/cancel', [App\Http\Controllers\CreatorSubscriptionController::class, 'cancel'])->name('cancel');
});

// Plan Subscriptions routes (legacy - will be deprecated)
Route::prefix('plan-subscriptions')->name('plan-subscriptions.')->middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\PlanSubscriptionController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\PlanSubscriptionController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\PlanSubscriptionController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\PlanSubscriptionController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\PlanSubscriptionController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\PlanSubscriptionController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\PlanSubscriptionController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/cancel', [App\Http\Controllers\PlanSubscriptionController::class, 'cancel'])->name('cancel');
    Route::post('/{id}/renew', [App\Http\Controllers\PlanSubscriptionController::class, 'renew'])->name('renew');
});

// Payment callback routes
Route::post('/payment/callback', [App\Http\Controllers\PaymentCallbackController::class, 'callback'])->name('payment.callback');
Route::get('/payment/return', [App\Http\Controllers\PaymentCallbackController::class, 'returnUrl'])->name('payment.return');

// Static pages routes
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/feedback', [PageController::class, 'feedback'])->name('feedback');
Route::get('/credits/withdraw', [PageController::class, 'creditsWithdraw'])->middleware('auth')->name('credits.withdraw');
Route::get('/points/rules', [PageController::class, 'pointsRules'])->name('points.rules');
Route::get('/about', [PageController::class, 'about'])->name('about');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
