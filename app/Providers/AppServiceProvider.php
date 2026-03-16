<?php

namespace App\Providers;

use App\Models\GlsInscription;
use App\Models\GroupApplication;
use App\Observers\GlsInscriptionObserver;
use App\Observers\GroupApplicationObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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

        // Rate limiter: max 30 Google Sheets API jobs per minute
        RateLimiter::for('google-sheets', function (object $job) {
            return Limit::perMinute(30);
        });

        // Observers
        GroupApplication::observe(GroupApplicationObserver::class);
        GlsInscription::observe(GlsInscriptionObserver::class);
    }
}
