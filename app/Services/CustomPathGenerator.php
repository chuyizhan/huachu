<?php

namespace App\Services;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CustomPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        // For videos, use organized structure: posts/{postId}/videos/{mediaId}/
        if ($media->collection_name === 'videos') {
            return "posts/{$media->model_id}/videos/{$media->id}/";
        }

        // For images, use organized structure: posts/{postId}/images/{mediaId}/
        if ($media->collection_name === 'images') {
            return "posts/{$media->model_id}/images/{$media->id}/";
        }

        // For other media, use default structure: {mediaId}/
        return $media->id . '/';
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    /**
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive-images/';
    }
}
