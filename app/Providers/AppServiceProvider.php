<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SalesRecord;
use App\Observers\SalesRecordObserver;

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
        // Register the Observer for raw sales data
        SalesRecord::observe(SalesRecordObserver::class);
    }
}