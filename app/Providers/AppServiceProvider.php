<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckSuspended;
use App\Http\Middleware\CheckPasswordReset;
use Illuminate\Support\Facades\Blade;


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
        // Ensure you are using Route and not Router
        Route::aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);
        Route::aliasMiddleware('checkSuspended', CheckSuspended::class);
        Route::aliasMiddleware('checkPasswordReset', CheckPasswordReset::class);
        view()->addNamespace('mail', resource_path('views/vendor/mail'));
    }
}
