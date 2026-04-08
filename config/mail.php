<?php

$mailScheme = env('MAIL_SCHEME');
$legacyEncryption = env('MAIL_ENCRYPTION');
$mailFromAddress = env('MAIL_FROM_ADDRESS');
$mailFromDomain = null;

if (! $mailScheme && $legacyEncryption && strtolower((string) $legacyEncryption) === 'ssl') {
    $mailScheme = 'smtps';
}

if (is_string($mailFromAddress) && str_contains($mailFromAddress, '@')) {
    $parts = explode('@', $mailFromAddress, 2);
    $mailFromDomain = $parts[1] ?: null;
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => $mailScheme,
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => env('MAIL_TIMEOUT'),
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                $mailFromDomain ?: (parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost')
            ),
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => explode(',', (string) env('MAIL_FAILOVER_MAILERS', 'smtp,log')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
        'name' => env('MAIL_FROM_NAME', 'DepEd DOCTRAX'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    */

    'markdown' => [
        'theme' => env('MAIL_MARKDOWN_THEME', 'default'),
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
];
