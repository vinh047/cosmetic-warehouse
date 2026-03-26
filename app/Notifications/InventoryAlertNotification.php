<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InventoryAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array $lowStocks Danh sách sản phẩm sắp hết
     * @param array $expiringBatches Danh sách lô hàng cận date
     */
    public function __construct(
        public array $lowStocks,
        public array $expiringBatches
    ) {}

    /**
     * Chọn kênh gửi thông báo. 
     * 'mail' để gửi email, 'database' để lưu vào bảng notifications.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Nội dung Email gửi đi
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Mẹo: Nếu bạn vẫn muốn dùng file template Blade cũ (InventoryAlertMail), 
        // bạn có thể đổi thành: return (new MailMessage)->view('emails.inventory_alert', ['lowStocks' => $this->lowStocks, ...]);
        
        $mail = (new MailMessage)
            ->subject('Cảnh báo Kho Hàng: Hết hàng / Cận date')
            ->greeting("Xin chào {$notifiable->name},")
            ->line('Hệ thống phát hiện một số mặt hàng cần lưu ý:');

        if (!empty($this->lowStocks)) {
            $mail->line('**--- DANH SÁCH SẮP HẾT HÀNG ---**');
            foreach ($this->lowStocks as $item) {
                $mail->line("- {$item->name} (SKU: {$item->sku}) | Kho: {$item->warehouse_name} | Tồn: {$item->total_qty}");
            }
        }

        if (!empty($this->expiringBatches)) {
            $mail->line('**--- DANH SÁCH CẬN DATE ---**');
            foreach ($this->expiringBatches as $batch) {
                $mail->line("- {$batch->name} | Lô: {$batch->batch_code} | Hết hạn: {$batch->expiry_date}");
            }
        }

        return $mail->action('Kiểm tra kho ngay', url('/admin/inventory'))
                    ->line('Vui lòng kiểm tra và có kế hoạch nhập hàng kịp thời.');
    }

    /**
     * Dữ liệu lưu vào Database (để hiển thị chuông thông báo trên web)
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Cảnh báo tồn kho & cận date',
            'message' => 'Có ' . count($this->lowStocks) . ' mã sắp hết hàng và ' . count($this->expiringBatches) . ' lô cận date.',
            'low_stocks' => $this->lowStocks,
            'expiring_batches' => $this->expiringBatches,
        ];
    }
}