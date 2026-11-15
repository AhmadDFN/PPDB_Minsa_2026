<?php

namespace App\Providers;

use App\Contracts\OtpDeliveryInterface;
use App\Services\Authentication\OtpDeliveryService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OtpDeliveryInterface::class, function () {
            return new OtpDeliveryService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
