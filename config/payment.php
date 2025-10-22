<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Third-party payment gateway settings
    |
    */

    'api_url' => env('PAYMENT_API_URL', 'http://202.95.8.8:1001'),

    'partner_id' => env('PAYMENT_PARTNER_ID', ''),

    'api_key' => env('PAYMENT_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Available payment methods
    |
    */

    'methods' => [
        'alipay' => [
            'name' => '支付宝',
            'enabled' => env('PAYMENT_ALIPAY_ENABLED', true),
        ],
        'wechat' => [
            'name' => '微信支付',
            'enabled' => env('PAYMENT_WECHAT_ENABLED', true),
        ],
        'bank' => [
            'name' => '网银',
            'enabled' => env('PAYMENT_BANK_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    |
    | URLs for payment callbacks
    |
    */

    'callback_url' => env('APP_URL') . '/payment/callback',
    'return_url' => env('APP_URL') . '/payment/return',
];
