<?php

namespace App\Providers;

use App\Models\Cadre;
use App\Models\MedicationReport;
use App\Models\Patient;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ([User::class, Patient::class, Cadre::class, MedicationReport::class] as $model) {
            $model::observe(AuditObserver::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
