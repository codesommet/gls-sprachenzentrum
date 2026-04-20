<?php

namespace App\Providers;

use App\Models\GlsInscription;
use App\Models\GroupApplication;
use App\Observers\GlsInscriptionObserver;
use App\Observers\GroupApplicationObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

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
        // Fix MySQL index length errors
        Schema::defaultStringLength(191);

        // Ensure Carbon always uses French locale (matches config/app.locale)
        Carbon::setLocale(config('app.locale', 'fr'));

        // Rate limiter: max 30 Google Sheets API jobs per minute
        RateLimiter::for('google-sheets', function (object $job) {
            return Limit::perMinute(30);
        });

        // Use Bootstrap 5 pagination views instead of Tailwind
        Paginator::useBootstrapFive();

        // Observers
        GroupApplication::observe(GroupApplicationObserver::class);
        GlsInscription::observe(GlsInscriptionObserver::class);
    }
}
