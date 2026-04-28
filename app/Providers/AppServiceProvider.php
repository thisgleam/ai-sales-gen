<?php

namespace App\Providers;

use App\Models\SalesPage;
use App\Observers\SalesPageObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        if (str_starts_with(config('app.url'), 'https')) {
            \URL::forceScheme('https');
        }

        SalesPage::observe(SalesPageObserver::class);

        RateLimiter::for('ai-generation', function (Request $request) {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();
            $response = fn (Request $request, array $headers) => response(
                'Limit harian AI Anda tercapai. Coba lagi nanti.',
                429,
                $headers
            );

            return [
                Limit::perMinute(5)->by('minute:'.$key)->response($response),
                Limit::perDay(50)->by('day:'.$key)->response($response),
            ];
        });
    }
}
