<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InventoryAlertMail extends Mailable 
{
    use Queueable, SerializesModels;

    /**
     * Khởi tạo class với PHP 8 Constructor Property Promotion
     * Ở Laravel 12, biến public sẽ tự động được truyền sang view (Blade)
     */
    public function __construct(
        public array $lowStocks = [],
        public array $expiringBatches = []
    ) {}

    /**
     * Tiêu đề của Email
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ [HỆ THỐNG] Cảnh báo Tồn kho & Hạn sử dụng - ' . now()->format('d/m/Y'),
        );
    }

    /**
     * Đường dẫn tới file giao diện (Blade view)
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inventory-alert',
        );
    }

    /**
     * (Tùy chọn) Đính kèm file nếu cần
     */
    public function attachments(): array
    {
        return [];
    }
}