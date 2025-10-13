<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Commission Percentage
    |--------------------------------------------------------------------------
    |
    | The percentage of revenue that the platform takes from paid posts.
    | This is deducted from the creator's earnings when a user purchases
    | access to a paid post.
    |
    */
    'commission_percentage' => env('PLATFORM_COMMISSION_PERCENTAGE', 30),
];
