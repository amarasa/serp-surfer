<?php

return [
    'application_name' => env('GOOGLE_APPLICATION_NAME', 'Your Application Name'),
    'client_id'        => env('GOOGLE_CLIENT_ID', 'Your Client ID'),
    'client_secret'    => env('GOOGLE_CLIENT_SECRET', 'Your Client Secret'),
    'redirect_uri'     => env('GOOGLE_REDIRECT_URI', 'Your Redirect URI'),
    'scopes'           => [
        \Google_Service_SearchConsole::WEBMASTERS_READONLY,
    ],
];
