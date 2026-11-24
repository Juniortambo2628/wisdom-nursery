<?php

return [
    // {{NURSERY_WHATSAPP}}: public number for wa.me links, 2547XXXXXXXX.
    'whatsapp' => env('NURSERY_WHATSAPP'),

    'ga4_id' => env('GA4_MEASUREMENT_ID'),

    // First order number is WN-1001.
    'order_number_offset' => 1000,

    'mpesa' => [
        'env' => env('MPESA_ENV', 'sandbox'),
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'type' => env('MPESA_TYPE', 'paybill'), // paybill | till
        'party_b' => env('MPESA_PARTY_B', env('MPESA_SHORTCODE')),
        'passkey' => env('MPESA_PASSKEY'),
        'account_reference' => env('MPESA_ACCOUNT_REFERENCE', 'Wisdom Nursery'),
        'callback_secret' => env('MPESA_CALLBACK_SECRET'),
        'base_urls' => [
            'sandbox' => 'https://sandbox.safaricom.co.ke',
            'production' => 'https://api.safaricom.co.ke',
        ],
    ],

    'sms' => [
        'env' => env('AT_ENV', 'sandbox'),
        'username' => env('AT_USERNAME', 'sandbox'),
        'api_key' => env('AT_API_KEY'),
        'sender_id' => env('AT_SENDER_ID'),
        'base_urls' => [
            'sandbox' => 'https://api.sandbox.africastalking.com',
            'production' => 'https://api.africastalking.com',
        ],
    ],

    'whatsapp_alerts' => [
        'driver' => env('WHATSAPP_DRIVER', 'log'), // log | cloud
        'token' => env('WHATSAPP_CLOUD_TOKEN'),
        'phone_number_id' => env('WHATSAPP_CLOUD_PHONE_NUMBER_ID'),
        'templates' => [
            'order_paid' => env('WHATSAPP_TEMPLATE_ORDER', 'new_order_alert'),
            'quote_requested' => env('WHATSAPP_TEMPLATE_QUOTE', 'new_quote_alert'),
        ],
        'language' => env('WHATSAPP_TEMPLATE_LANG', 'en'),
        'api_version' => 'v21.0',
    ],
];
