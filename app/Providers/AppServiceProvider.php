<?php

namespace App\Providers;

use App\Enums\InventoryReferenceType;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        Relation::enforceMorphMap([
            InventoryReferenceType::ORDER->value      => \App\Models\Order::class,
        ]);
    }
}
