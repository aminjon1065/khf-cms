<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Middleware\SetApiLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:sanctum', SetApiLocale::class, 'throttle:api'])
    ->group(function () {
        $routes = function (): void {
            Route::get('/health', HealthController::class);

            Route::get('/news', [NewsController::class, 'index']);
            Route::get('/news/{idOrSlug}/related', [NewsController::class, 'related']);
            Route::get('/news/{idOrSlug}', [NewsController::class, 'show']);
        };

        $routes();

        Route::prefix('{locale}')
            ->whereIn('locale', ['tg', 'ru', 'en'])
            ->group($routes);
    });

// Public view beacon — no token, no locale prefix, throttled per IP.
Route::prefix('v1')
    ->middleware('throttle:10,1')
    ->group(function () {
        Route::post('/news/{idOrSlug}/view', [NewsController::class, 'view']);
    });

if (app()->environment('testing')) {
    Route::prefix('v1')
        ->middleware(['throttle:2,1'])
        ->group(function () {
            Route::get('/throttle-probe', fn () => response()->json(['data' => ['ok' => true]]));
            Route::post('/validation-probe', function (Request $request) {
                $request->validate(['name' => 'required|string']);
            });
        });
}
