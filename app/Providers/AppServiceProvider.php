<?php

namespace App\Providers;

use App\Enums\InventoryReferenceType;
use App\Events\OrderCreated;
use App\Listeners\CheckStockAfterOrderListener;
use App\Listeners\SendOrderCreatedNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Đăng ký Events & Listeners
        Event::listen(
            OrderCreated::class,
            CheckStockAfterOrderListener::class,
        );

        Event::listen(
            OrderCreated::class,
            SendOrderCreatedNotification::class,
        );

        Relation::enforceMorphMap([
            InventoryReferenceType::ORDER->value => \App\Models\Order::class,
            'user' => User::class,
        ]);

        Password::defaults(function () {
            return Password::min(6)
                ->letters()
                ->numbers();
        });
    }
}