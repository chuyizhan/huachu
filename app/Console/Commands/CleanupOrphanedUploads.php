<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TemporaryUpload;
use Carbon\Carbon;

class CleanupOrphanedUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:orphaned-uploads {--hours=24 : Delete uploads older than this many hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete temporary uploads that were never attached to a post';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $threshold = Carbon::now()->subHours($hours);

        $this->info("Cleaning up temporary uploads older than {$hours} hours (before {$threshold})...");

        // Find orphaned temporary uploads
        $orphanedUploads = TemporaryUpload::where('created_at', '<', $threshold)->get();

        if ($orphanedUploads->isEmpty()) {
            $this->info('No orphaned uploads found.');
            return Command::SUCCESS;
        }

        $count = $orphanedUploads->count();
        $this->info("Found {$count} orphaned upload(s) to delete.");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($orphanedUploads as $upload) {
            // Deleting the model will also delete associated media files via Spatie Media Library
            $upload->delete();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully cleaned up {$count} orphaned upload(s).");

        return Command::SUCCESS;
    }
}
