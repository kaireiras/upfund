<?php

return [
        /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    |
    | Server key digunakan untuk server-to-server communication.
    | JANGAN expose key ini di frontend.
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    |
    | Client key digunakan di frontend untuk Snap.js
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Production Mode
    |--------------------------------------------------------------------------
    |
    | Set true untuk production, false untuk sandbox.
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Sanitization
    |--------------------------------------------------------------------------
    |
    | Set true untuk enable sanitization pada request parameters.
    |
    */
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    /*
    |--------------------------------------------------------------------------
    | 3D Secure
    |--------------------------------------------------------------------------
    |
    | Set true untuk enable 3D Secure pada credit card transactions.
    |
    */
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    /*
    |--------------------------------------------------------------------------
    | Notification URL
    |--------------------------------------------------------------------------
    |
    | URL untuk menerima payment notification dari Midtrans.
    |
    */
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Snap URL
    |--------------------------------------------------------------------------
    |
    | Base URL untuk Snap.js
    |
    */
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? '<https://app.midtrans.com/snap/snap.js>'
        : '<https://app.sandbox.midtrans.com/snap/snap.js>',
];
