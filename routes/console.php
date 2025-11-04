<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule cleanup of orphaned uploads (videos/images that were uploaded but never attached to a post)
// Runs daily at 3 AM and deletes uploads older than 24 hours
Schedule::command('cleanup:orphaned-uploads')->dailyAt('03:00');

// Schedule expiration of user subscriptions
// Runs every hour to check and expire subscriptions that have passed their expiration date
Schedule::command('subscriptions:expire')->hourly();
