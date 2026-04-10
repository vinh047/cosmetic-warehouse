<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Gửi notification cho tất cả admin và manager
        $admins = User::whereIn('role', ['admin', 'manager'])->get();

        foreach ($admins as $admin) {
            $admin->notify(new OrderCreatedNotification($order));
        }
    }
}