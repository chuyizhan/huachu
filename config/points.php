<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Points Rewards Configuration
    |--------------------------------------------------------------------------
    |
    | Configure points rewards for various user actions.
    |
    */

    'post_creation' => [
        'enabled' => env('POINTS_POST_CREATION_ENABLED', true),
        // Note: Category-specific points are now configured per category in the database
        // These values are fallback defaults if category doesn't have specific settings
        'default_points' => env('POINTS_POST_CREATION_DEFAULT', 10),
        'default_minimum_images' => env('POINTS_POST_CREATION_DEFAULT_MIN_IMAGES', 5),
    ],
];
