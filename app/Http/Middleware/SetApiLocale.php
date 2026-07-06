<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    /**
     * @var array<string, string>
     */
    private const LOCALE_MAP = [
        'tg' => 'tj',
        'ru' => 'ru',
        'en' => 'en',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = self::LOCALE_MAP[$request->route('locale')] ?? 'tj';

        app()->setLocale($locale);
        $request->attributes->set('api_locale', $locale);

        return $next($request);
    }
}
