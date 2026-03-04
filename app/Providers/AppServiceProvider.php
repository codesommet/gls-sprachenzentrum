<?php

namespace App\Providers;

use App\Models\GlsInscription;
use App\Models\GroupApplication;
use App\Observers\GlsInscriptionObserver;
use App\Observers\GroupApplicationObserver;
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

        // Observers
        GroupApplication::observe(GroupApplicationObserver::class);
        GlsInscription::observe(GlsInscriptionObserver::class);
    }
}
