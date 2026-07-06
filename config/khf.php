<?php

return [

    'api_user_email' => env('API_TOKEN_USER_EMAIL', 'api@khf.local'),

    'frontend_token_name' => 'frontend',

    /*
    |--------------------------------------------------------------------------
    | Seed administrator (ToR §4)
    |--------------------------------------------------------------------------
    |
    | The first admin account created by DatabaseSeeder. Credentials come from
    | .env so they are never hard-coded. If ADMIN_PASSWORD is empty the seeder
    | generates a strong random password and prints it once to the console.
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Администратор'),
        'email' => env('ADMIN_EMAIL', 'admin@khf.tj'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend ISR revalidation (docs/API-CONTRACT.md §5, ToR §8)
    |--------------------------------------------------------------------------
    |
    | Secrets live only in .env (PROJECT.md). The CMS "Ревалидация" page reads
    | these values and exposes the "Проверить" / "Сбросить весь кеш" actions.
    */
    'revalidate' => [
        'frontend_url' => env('FRONTEND_URL'),
        'secret' => env('REVALIDATE_SECRET'),
        'path' => '/api/revalidate',
    ],

];
