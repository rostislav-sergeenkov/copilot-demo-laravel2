<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
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
        // Force HTTPS URLs in production (works with TrustProxies middleware)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Validate required authentication environment variables
        // Skip validation during console commands (composer install, artisan commands, etc.)
        if (! $this->app->runningInConsole()) {
            if (empty(config('auth.custom.username')) || empty(config('auth.custom.password_hash'))) {
                throw new \RuntimeException(
                    'AUTH_USERNAME and PASSWORD_HASH environment variables are required. ' .
                        'Configure them in .env file.'
                );
            }
        }

        // Register custom @auth directive
        Blade::if('auth', function () {
            return session('authenticated') === true;
        });
    }
}
