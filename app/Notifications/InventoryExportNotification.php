<?php

namespace App\Notifications;

use App\Mail\InventoryExportMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InventoryExportNotification extends Notification 
{
    use Queueable;

    public function __construct(
        public string $filePath,
        public string $fileName,
        public int $month,
        public int $year
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Gọi file Mail đã cấu hình đính kèm sẵn
     */
    public function toMail(object $notifiable)
    {
        return new InventoryExportMail( // ← bỏ ->to($notifiable->email)
            $this->filePath,
            $this->fileName,
            $this->month,
            $this->year
        );
    }

    /**
     * Lưu vào Database để user thấy thông báo trên chuông trang web
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Báo cáo tồn kho tháng {$this->month}/{$this->year}",
            'message' => 'Hệ thống đã xuất báo cáo Excel thành công. Vui lòng kiểm tra Email.',
            'file_name' => $this->fileName,
        ];
    }
}