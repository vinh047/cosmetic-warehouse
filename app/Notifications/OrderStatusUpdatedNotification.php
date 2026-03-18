<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Order $order,
        private readonly string $fromStatus,
        private readonly string $toStatus
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Cap nhat trang thai don hang',
            'message' => "Don {$this->order->order_code} da chuyen tu {$this->fromStatus} sang {$this->toStatus}.",
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'updated_at' => now()->toDateTimeString(),
        ];
    }
}
