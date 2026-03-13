<?php

return [



    'default' => env('MAIL_MAILER', 'log'),



    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => (function (): ?string {
                $normalize = static function (?string $value): ?string {
                    if (!is_string($value)) {
                        return null;
                    }

                    $value = trim($value);
                    if ($value === '') {
                        return null;
                    }

                    // Gmail app passwords are often copied with spaces (xxxx xxxx xxxx xxxx).
                    return preg_replace('/\s+/', '', $value);
                };

                $passwordFile = env('MAIL_PASSWORD_FILE');

                if (is_string($passwordFile) && $passwordFile !== '') {
                    $resolvedPath = $passwordFile;

                    // Resolve relative paths from Laravel project root.
                    if (!preg_match('/^(?:[A-Za-z]:\\\\|\\\\\\\\|\\/)/', $passwordFile)) {
                        $resolvedPath = base_path($passwordFile);
                    }

                    if (is_file($resolvedPath)) {
                        return $normalize((string) file_get_contents($resolvedPath));
                    }
                }

                return $normalize(env('MAIL_PASSWORD'));
            })(),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
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
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],


    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

];
