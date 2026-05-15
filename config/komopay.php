<?php

/*
 * KomoPay / Lipa backend integration.
 *
 * Single source of truth for the API contract is
 * Customer_Merchant_Frontend_Specification.md at the repo root.
 *
 * Mock vs real switch:
 *   KOMOPAY_USE_MOCK_API=true  -> in-process Mock* implementations (no HTTP)
 *   KOMOPAY_USE_MOCK_API=false -> Http* implementations against KOMOPAY_API_BASE_URL
 */

return [

    'base_url' => rtrim((string) env('KOMOPAY_API_BASE_URL', 'http://localhost:8080'), '/'),

    'use_mock' => filter_var(env('KOMOPAY_USE_MOCK_API', true), FILTER_VALIDATE_BOOLEAN),

    'api_key' => env('KOMOPAY_API_KEY', ''),

    'timeout' => (int) env('KOMOPAY_API_TIMEOUT', 10),

    // API path prefix per spec section 5 (`/api/v1/...`).
    'api_prefix' => '/api/v1',

    // Default country code used by the portal.
    'default_country_code' => '269',

    'session' => [
        // Session keys used to persist the JWT envelope per actor type.
        'customer_token_key' => 'komopay.customer.tokens',
        'merchant_token_key' => 'komopay.merchant.tokens',
    ],
];
