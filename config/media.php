<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Media Disk
    |--------------------------------------------------------------------------
    |
    | This option controls the default disk that will be used for storing
    | media files like images and videos. You may configure this to use
    | local storage ('public'), cloud storage like Amazon S3 ('s3'),
    | or Wasabi ('wasabi').
    |
    | Supported: "public", "s3", "wasabi"
    |
    */

    'disk' => env('MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Media Collections Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure settings specific to different media collections
    | such as maximum file sizes, allowed mime types, etc.
    |
    */

    'collections' => [
        'images' => [
            'disk' => env('MEDIA_IMAGES_DISK', env('MEDIA_DISK', 'public')),
            'max_file_size' => 5120, // 5MB in KB
            'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        ],
        'videos' => [
            'disk' => env('MEDIA_VIDEOS_DISK', env('MEDIA_DISK', 'public')),
            'max_file_size' => 102400, // 100MB in KB
            'allowed_mime_types' => ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'],
        ],
    ],

];
