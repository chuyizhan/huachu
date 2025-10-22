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
        'points' => env('POINTS_POST_CREATION', 10),
        'minimum_images' => env('POINTS_POST_CREATION_MIN_IMAGES', 5),
    ],
];
