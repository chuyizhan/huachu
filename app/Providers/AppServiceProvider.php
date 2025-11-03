<?php

namespace App\Providers;

use App\Models\User;
use App\Models\UserSubscription;
use App\Observers\UserObserver;
use App\Observers\UserSubscriptionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register User observer for auto-creating creator profiles
        User::observe(UserObserver::class);

        // Register UserSubscription observer for updating revenue tracking
        UserSubscription::observe(UserSubscriptionObserver::class);
    }
}
