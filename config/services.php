<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'yfapi' => [
        'enabled' => (bool) env('YFAPI_ENABLED', false),
        'key' => env('YFAPI_KEY'),
        'url' => env('YFAPI_URL', 'https://yfapi.net'),
    ],

    'yahoo_finance' => [
        'search_url' => env('YAHOO_FINANCE_SEARCH_URL', 'https://query1.finance.yahoo.com/v1/finance/search'),
        'chart_url' => env('YAHOO_FINANCE_CHART_URL', 'https://query1.finance.yahoo.com/v8/finance/chart'),
    ],

    'extraction' => [
        'queue' => env('DATA_EXTRACTION_QUEUE', 'extraction'),
        'timeout' => (int) env('DATA_EXTRACTION_HTTP_TIMEOUT', 20),
        'retry' => (int) env('DATA_EXTRACTION_HTTP_RETRY', 3),
        'retry_sleep_ms' => (int) env('DATA_EXTRACTION_HTTP_RETRY_SLEEP_MS', 500),
        'job_timeout' => (int) env('DATA_EXTRACTION_JOB_TIMEOUT', 120),
        'job_tries' => (int) env('DATA_EXTRACTION_JOB_TRIES', 1),
        'sources' => [
            'idx' => [
                'bond_url' => env('IDX_BOND_EXTRACTION_URL'),
                'stock_url' => env('IDX_STOCK_EXTRACTION_URL'),
            ],
            'phei' => [
                'bond_url' => env('PHEI_BOND_EXTRACTION_URL'),
                'news_url' => env('PHEI_NEWS_EXTRACTION_URL'),
            ],
        ],
    ],

    'groq' => [
        'key'   => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'url'   => 'https://api.groq.com/openai/v1/chat/completions',
    ],

    'openai' => [
        'key'   => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
        'url'   => 'https://api.openai.com/v1/chat/completions',
    ],

];
