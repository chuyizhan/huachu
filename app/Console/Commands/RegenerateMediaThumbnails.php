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
    protected $signature = 'media:regenerate-thumbnails {--collection=images} {--only-missing : Only generate missing conversions}';

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
        $onlyMissing = $this->option('only-missing');

        $this->info("Regenerating thumbnails for collection: {$collection}");

        $media = Media::where('collection_name', $collection)->get();

        if ($media->isEmpty()) {
            $this->warn('No media found in this collection.');
            return Command::SUCCESS;
        }

        $this->info("Found {$media->count()} media items.");
        $progressBar = $this->output->createProgressBar($media->count());
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        foreach ($media as $mediaItem) {
            try {
                // Get the model that owns this media
                $model = $mediaItem->model;

                if (!$model) {
                    $this->error("\nMedia ID {$mediaItem->id} has no associated model.");
                    $errorCount++;
                    $progressBar->advance();
                    continue;
                }

                // Check if we should skip already generated conversions
                if ($onlyMissing) {
                    $hasThumb = $mediaItem->hasGeneratedConversion('thumb');
                    $hasMedium = $mediaItem->hasGeneratedConversion('medium');

                    if ($hasThumb && $hasMedium) {
                        $skippedCount++;
                        $progressBar->advance();
                        continue;
                    }
                }

                // Get original file path
                $pathToImage = $mediaItem->getPath();

                if (!file_exists($pathToImage)) {
                    $this->error("\nOriginal file not found for media ID {$mediaItem->id}: {$pathToImage}");
                    $errorCount++;
                    $progressBar->advance();
                    continue;
                }

                // Use Spatie's built-in conversion job handler
                (new \Spatie\MediaLibrary\Conversions\FileManipulator())
                    ->createDerivedFiles($mediaItem);

                $successCount++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to regenerate conversions for media ID {$mediaItem->id}: {$e->getMessage()}");
                $errorCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->newLine(2);
        $this->info("Thumbnail regeneration complete!");
        $this->info("Success: {$successCount}");

        if ($skippedCount > 0) {
            $this->info("Skipped (already exists): {$skippedCount}");
        }

        if ($errorCount > 0) {
            $this->warn("Errors: {$errorCount}");
        }

        return Command::SUCCESS;
    }
}
