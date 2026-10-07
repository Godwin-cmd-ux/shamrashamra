<?php

/**
 * Test bootstrap.
 *
 * The Freebuff workspace injects real environment variables into $_SERVER for
 * every command (e.g. APP_ENV=local, DB_CONNECTION=pgsql). PHPUnit's <env>
 * entries do not override $_SERVER, so we pin the hermetic test values here —
 * before the application boots — to guarantee tests run on in-memory SQLite
 * and never touch the development database.
 */
$testEnv = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'true',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
    'BCRYPT_ROUNDS' => '4',
    'PULSE_ENABLED' => 'false',
    'TELESCOPE_ENABLED' => 'false',
    'NIGHTWATCH_ENABLED' => 'false',
];

foreach ($testEnv as $key => $value) {
    $_SERVER[$key] = $value;
    $_ENV[$key] = $value;
    putenv("{$key}={$value}");
}

require dirname(__DIR__).'/vendor/autoload.php';
