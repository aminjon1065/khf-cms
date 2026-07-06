<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Middleware\SetApiLocale;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:sanctum', SetApiLocale::class, 'throttle:api'])
    ->group(function () {
        $routes = function (): void {
            Route::get('/health', HealthController::class);
        };

        $routes();

        Route::prefix('{locale}')
            ->whereIn('locale', ['tg', 'ru', 'en'])
            ->group($routes);
    });

if (app()->environment('testing')) {
    Route::prefix('v1')
        ->middleware(['throttle:2,1'])
        ->group(function () {
            Route::get('/throttle-probe', fn () => response()->json(['data' => ['ok' => true]]));
            Route::post('/validation-probe', function (Illuminate\Http\Request $request) {
                $request->validate(['name' => 'required|string']);
            });
        });
}
