<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
        // Validate required authentication environment variables
        if (empty(env('AUTH_USERNAME')) || empty(env('PASSWORD_HASH'))) {
            throw new \RuntimeException(
                'AUTH_USERNAME and PASSWORD_HASH environment variables are required. ' .
                    'Configure them in .env file.'
            );
        }

        // Register custom @auth directive
        Blade::if('auth', function () {
            return session('authenticated') === true;
        });
    }
}
