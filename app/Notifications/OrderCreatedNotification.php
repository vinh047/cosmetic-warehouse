<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order)
    {
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
            'title' => 'Co don hang moi',
            'message' => "Don {$this->order->order_code} vua duoc tao.",
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'customer_name' => $this->order->customer_name,
            'channel' => $this->order->channel?->value,
            'status' => $this->order->status?->value,
            'total_price' => (string) $this->order->total_price,
            'created_at' => $this->order->created_at?->toDateTimeString(),
        ];
    }
}
