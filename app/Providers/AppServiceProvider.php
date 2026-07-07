<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Public write endpoints each get their OWN per-IP bucket. A numeric
        // throttle (throttle:5,1) keys only on domain|ip, so every numeric-
        // throttled route collides on one counter — a citizen reading news
        // (each page firing the view beacon) could exhaust the emergency-report
        // budget. Named limiters namespace the key by name, keeping each
        // endpoint's 5 rpm/IP window isolated (docs/API-CONTRACT.md §4).
        RateLimiter::for('reports', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('subscriptions', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('news-view', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // Password policy for admin/editor accounts (ToR §10). Stricter in
        // production; relaxed locally so dev/test fixtures stay simple.
        Password::defaults(function (): Password {
            $rule = Password::min(8)->letters()->numbers();

            return $this->app->isProduction()
                ? $rule->min(12)->mixedCase()->symbols()->uncompromised()
                : $rule;
        });
    }
}
