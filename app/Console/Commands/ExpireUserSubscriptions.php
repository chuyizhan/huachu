<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use Illuminate\Console\Command;

class ExpireUserSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire user subscriptions that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        // Find all active subscriptions that have passed their expiration date
        $expiredSubscriptions = UserSubscription::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expiredSubscriptions->count()} expired subscriptions.");

        $bar = $this->output->createProgressBar($expiredSubscriptions->count());
        $bar->start();

        $expired = 0;

        foreach ($expiredSubscriptions as $subscription) {
            // Update status to expired
            $subscription->update(['status' => 'expired']);
            $expired++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Successfully expired {$expired} subscriptions.");

        return Command::SUCCESS;
    }
}
