<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dedicated Recruitment Mail Configuration
    |--------------------------------------------------------------------------
    |
    | Separate mail configuration for recruitment activities (invitations,
    | offers, updates). Allows using a different sending address or provider
    | without altering the primary system notification mailer.
    |
    */
    'mail' => [
        'enabled'      => env('RECRUITMENT_SEND_INTERVIEW_EMAIL', false),
        'mailer'       => env('RECRUITMENT_MAIL_MAILER', env('MAIL_MAILER', 'smtp')),
        'host'         => env('RECRUITMENT_MAIL_HOST', env('MAIL_HOST', '127.0.0.1')),
        'port'         => env('RECRUITMENT_MAIL_PORT', env('MAIL_PORT', 2525)),
        'username'     => env('RECRUITMENT_MAIL_USERNAME', env('MAIL_USERNAME')),
        'password'     => env('RECRUITMENT_MAIL_PASSWORD', env('MAIL_PASSWORD')),
        'encryption'   => env('RECRUITMENT_MAIL_ENCRYPTION', env('MAIL_ENCRYPTION')),
        'from_address' => env('RECRUITMENT_MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'recruitment@mutiaraharapan.sch.id')),
        'from_name'    => env('RECRUITMENT_MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'MHIS Recruitment Team')),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage Disks & Limits
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'assessments_path' => 'recruitment/assessments',
        'evaluations_path' => 'recruitment/evaluations',
        'max_assessment_size_kb' => 10240, // 10MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Phase 5: Action Required Thresholds & Dashboard Settings
    |--------------------------------------------------------------------------
    */
    'action_required' => [
        'interview_evaluation_days' => (int) env('RECRUITMENT_PENDING_EVALUATION_DAYS', 3),
        'offer_pending_days'        => (int) env('RECRUITMENT_PENDING_OFFER_DAYS', 3),
        'inactive_candidate_days'   => (int) env('RECRUITMENT_INACTIVE_CANDIDATE_DAYS', 30),
    ],

    'dashboard_cache_ttl' => (int) env('RECRUITMENT_DASHBOARD_CACHE_TTL', 60),
];

