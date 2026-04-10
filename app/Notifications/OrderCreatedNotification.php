<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail', 'database']; // ← thêm 'mail'
    }

    /**
     * Gửi email thông báo đơn hàng mới cho admin/manager
     */
    public function toMail(object $notifiable): MailMessage // ← thêm hàm này
    {
        return (new MailMessage)
            ->subject('🛒 Đơn hàng mới: ' . $this->order->order_code)
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Một đơn hàng mới vừa được tạo trên hệ thống.')
            ->line('**Mã đơn:** ' . $this->order->order_code)
            ->line('**Khách hàng:** ' . $this->order->customer_name)
            ->line('**Tổng tiền:** ' . number_format($this->order->total_price) . ' VNĐ')
            ->action('Xem đơn hàng', url('/orders/' . $this->order->id))
            ->salutation('Trân trọng, Hệ thống Cosmetic Warehouse');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Có đơn hàng mới',
            'message' => "Đơn {$this->order->order_code} vừa được tạo.",
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