<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RegenerateMediaThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:regenerate-thumbnails {--collection=images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate media thumbnails for existing images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $collection = $this->option('collection');

        $this->info("Regenerating thumbnails for collection: {$collection}");

        $media = Media::where('collection_name', $collection)->get();

        if ($media->isEmpty()) {
            $this->warn('No media found in this collection.');
            return Command::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar($media->count());
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($media as $mediaItem) {
            try {
                // Regenerate all conversions for this media item
                $mediaItem->regenerateConversions();
                $successCount++;
            } catch (\Exception $e) {
                $this->error("\nFailed to regenerate conversions for media ID {$mediaItem->id}: {$e->getMessage()}");
                $errorCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->newLine(2);
        $this->info("Thumbnail regeneration complete!");
        $this->info("Success: {$successCount}");

        if ($errorCount > 0) {
            $this->warn("Errors: {$errorCount}");
        }

        return Command::SUCCESS;
    }
}
