<?php

test('env example documents all required M1 variables', function () {
    $contents = file_get_contents(dirname(__DIR__, 2).'/.env.example');

    $requiredKeys = [
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
        'FRONTEND_URL',
        'REVALIDATE_SECRET',
        'MEDIA_DISK',
        'MAIL_MAILER',
        'MAIL_HOST',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'MAIL_PASSWORD',
        'MAIL_FROM_ADDRESS',
        'MAIL_FROM_NAME',
        'QUEUE_CONNECTION',
        'API_TOKEN_USER_EMAIL',
    ];

    foreach ($requiredKeys as $key) {
        expect($contents)->toContain("{$key}=");
    }

    expect($contents)->toContain('DB_CONNECTION=mysql');
    expect($contents)->toContain('QUEUE_CONNECTION=database');
});
