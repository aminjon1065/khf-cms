<?php

return [

    'api_user_email' => env('API_TOKEN_USER_EMAIL', 'api@khf.local'),

    'frontend_token_name' => 'frontend',

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
