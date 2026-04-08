<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Brave Search API Key
    |--------------------------------------------------------------------------
    | Your Brave Search subscription token.
    | Get one at: https://api.search.brave.com/
    */
    'api_key' => env('BRAVE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    | Override only when using a proxy or staging environment.
    */
    'base_url' => env('BRAVE_BASE_URL', 'https://api.search.brave.com'),

    /*
    |--------------------------------------------------------------------------
    | Search Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'count'       => 20,
        'safesearch'  => 'strict',
        'search_lang' => 'en',
        'country'     => 'us',
    ],
];
