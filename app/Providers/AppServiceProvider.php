<?php

namespace App\Providers;

use App\Contracts\TransactionAuthorization;
use App\Contracts\TransactionNotification;
use App\Services\DeviToolsTransactionAuthorization;
use App\Services\DeviToolsTransactionNotification;
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
        $this->app->bind(
            TransactionAuthorization::class,
            DeviToolsTransactionAuthorization::class
        );

        $this->app->bind(
            TransactionNotification::class,
            DeviToolsTransactionNotification::class
        );
    }
}
