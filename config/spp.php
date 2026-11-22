<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bank Transfer Info
    |--------------------------------------------------------------------------
    | Used in student dashboard and WhatsApp reminder messages.
    */
    'bank_info' => [
        'bank' => env('SPP_BANK_NAME', 'BCA'),
        'account_number' => env('SPP_BANK_ACCOUNT', '7670321988'),
        'account_name' => env('SPP_BANK_OWNER', 'Bunny Gawah'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin WhatsApp
    |--------------------------------------------------------------------------
    */
    'admin_whatsapp' => env('SPP_ADMIN_WA', '628123456789'),

    /*
    |--------------------------------------------------------------------------
    | School Info
    |--------------------------------------------------------------------------
    */
    'school_name' => env('SPP_SCHOOL_NAME', 'Universal School'),
    'school_address' => env('SPP_SCHOOL_ADDRESS', 'Jl. Pendidikan No. 1'),
];
