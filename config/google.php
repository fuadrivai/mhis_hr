<?php

return [
    'service_account_json' => storage_path('google-service-account.json'),
    'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID', null),
];
