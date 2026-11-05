<?php

return [
    'currency' => strtoupper(env('COMMERCE_CURRENCY', 'EUR')),
    'prices_include_tax' => (bool) env('PRICES_INCLUDE_TAX', true),
    'stock_reservation_minutes' => 30,
    'business' => [
        'legal_name' => env('BUSINESS_LEGAL_NAME'),
        'tax_id' => env('BUSINESS_TAX_ID'),
        'address' => env('BUSINESS_ADDRESS'),
        'city' => env('BUSINESS_CITY'),
        'postal_code' => env('BUSINESS_POSTAL_CODE'),
        'country_code' => env('BUSINESS_COUNTRY_CODE'),
        'email' => env('BUSINESS_EMAIL'),
    ],
];
