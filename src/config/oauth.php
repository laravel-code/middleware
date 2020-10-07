<?php

return [
    'host' => env('AUTHORIZATION_API_HOST'),
    'token' => env('AUTHORIZATION_API_TOKEN'),
    'client_id' => env('AUTHORIZATION_API_CLIENT_ID'),
    'client_secret' => env('AUTHORIZATION_API_CLIENT_SECRET'),
    'scopes' => env('AUTHORIZATION_API_SCOPES'),
    'public_key' => env('AUTHORIZATION_PUBLIC_KEY', storage_path('oauth-public.key')),
];
