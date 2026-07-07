<?php

use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\RegionController;
use App\Http\Controllers\Api\V1\StructureController;
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

            Route::get('/home/slides', [HomeController::class, 'slides']);
            Route::get('/home', [HomeController::class, 'index']);

            Route::get('/documents', [DocumentController::class, 'index']);

            Route::get('/structure', [StructureController::class, 'index']);

            Route::get('/activities', [ActivityController::class, 'index']);

            Route::get('/regions', [RegionController::class, 'index']);
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
