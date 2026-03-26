<?php

namespace App\Notifications;

use App\Mail\DailyRevenueMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class DailyRevenueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Carbon $date,
        public int $totalOrders,
        public float $totalRevenue,
        public int $totalProductsSold
    ) {}

    /**
     * Gửi qua Mail và lưu vào Database để hiển thị trên web
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }


    public function toMail(object $notifiable)
    {
        return (new DailyRevenueMail(
            $this->date,
            $this->totalOrders,
            $this->totalRevenue,
            $this->totalProductsSold
        ))->to($notifiable->email);
    }

    /**
     * Lưu dữ liệu vào bảng notifications
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Báo cáo chốt ca ngày ' . $this->date->format('d/m/Y'),
            'message' => "Doanh thu: " . number_format($this->totalRevenue, 0, ',', '.') . " VNĐ từ " . $this->totalOrders . " đơn hàng.",
            'date' => $this->date->toDateString(),
            'total_revenue' => $this->totalRevenue,
            'total_orders' => $this->totalOrders,
            'total_products_sold' => $this->totalProductsSold,
        ];
    }
}