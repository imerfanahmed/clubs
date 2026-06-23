<?php

return [
    'currency' => env('CASHIER_CURRENCY', 'gbp'),
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],
    'currency_locale' => 'en_GB',
    'payment_notification' => true,
    'currency_locale' => 'en_GB',
];
