<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // ✅ Ensure assets, links, and routes automatically use HTTPS when on live hosting or behind SSL proxy (excluding localhost)
        if (
            !app()->isLocal() &&
            !in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1']) &&
            (
                config('app.env') === 'production' || 
                request()->server('HTTPS') === 'on' || 
                request()->header('x-forwarded-proto') === 'https' || 
                request()->header('X-Forwarded-Proto') === 'https' ||
                (config('app.url') && str_starts_with(config('app.url'), 'https://'))
            )
        ) {
            URL::forceScheme('https');
        }
    }
}

