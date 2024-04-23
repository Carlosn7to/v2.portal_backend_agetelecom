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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'voalle' => [
        'client_id' => env('VOALLE_API_CLIENT_ID'),
        'client_secret' => env('VOALLE_API_CLIENT_SECRET'),
        'syndata' => env('VOALLE_API_SYNDATA'),
    ],
    'ldapOld' => [
        'host' => env('LDAP_OLD_HOST'),
        'base_dn' => env('LDAP_OLD_BASE_DN'),
        'username' => env('LDAP_OLD_USERNAME'),
        'password' => env('LDAP_OLD_PASSWORD'),
    ],
    'ldapNew' => [
        'host' => env('LDAP_NEW_HOST'),
        'base_dn' => env('LDAP_NEW_BASE_DN'),
        'username' => env('LDAP_NEW_USERNAME'),
        'password' => env('LDAP_NEW_PASSWORD'),
    ],
    'portal' => [
        'user_key' => env('USER_KEY_PORTAL'),
    ]
];
