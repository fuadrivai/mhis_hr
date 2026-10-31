<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    | Supported: "openai", "gemini", "mock"
    */
    'provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key'  => env('OPENAI_API_KEY', env('AI_API_KEY', '')),
        'model'    => env('OPENAI_MODEL', env('AI_MODEL', 'gpt-4o-mini')),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini Configuration
    |--------------------------------------------------------------------------
    */
    'gemini' => [
        'api_key'  => env('GEMINI_API_KEY', env('AI_API_KEY', '')),
        'model'    => env('GEMINI_MODEL', env('AI_MODEL', 'gemini-1.5-flash')),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scoring & General AI Settings
    |--------------------------------------------------------------------------
    */
    'timeout'             => (int) env('AI_TIMEOUT', 45),
    'max_tokens'          => (int) env('AI_MAX_TOKENS', 4096),
    'mandatory_score_cap' => (int) env('AI_MANDATORY_SCORE_CAP', 60),
    'prompt_version'      => 'v1',
    'consent_v2_version'  => 'candidate-privacy-v2',
];

