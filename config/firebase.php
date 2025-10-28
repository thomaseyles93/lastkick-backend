<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | You can configure Firebase-related URLs, project info, and credentials here.
    | These values will be loaded from your .env file.
    |
    */

    'keys_url' => env('FIREBASE_KEYS_URL', 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'client_email' => env('FIREBASE_CLIENT_EMAIL'),
    'private_key' => env('FIREBASE_PRIVATE_KEY'),
];
