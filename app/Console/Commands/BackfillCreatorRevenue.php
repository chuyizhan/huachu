<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class BackfillCreatorRevenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'creator:backfill-revenue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill total_earnings and total_platform_share for all creator profiles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to backfill creator revenue data...');

        $creators = User::where('is_creator', true)
            ->with('creatorProfile')
            ->get();

        $this->info("Found {$creators->count()} creators to process.");

        $bar = $this->output->createProgressBar($creators->count());
        $bar->start();

        $updated = 0;
        $skipped = 0;

        foreach ($creators as $creator) {
            $profile = $creator->creatorProfile;

            if (!$profile) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Calculate totals from existing subscriptions
            $totalEarnings = $creator->receivedSubscriptions()->sum('creator_amount') ?? 0;
            $totalPlatformShare = $creator->receivedSubscriptions()->sum('platform_amount') ?? 0;

            // Update the profile
            $profile->update([
                'total_earnings' => $totalEarnings,
                'total_platform_share' => $totalPlatformShare,
            ]);

            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Backfill complete!");
        $this->info("  Updated: {$updated} creator profiles");
        if ($skipped > 0) {
            $this->warn("  Skipped: {$skipped} creators without profiles");
        }

        return Command::SUCCESS;
    }
}
