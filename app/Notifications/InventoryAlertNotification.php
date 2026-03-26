<?php

namespace App\Notifications;

use App\Mail\InventoryAlertMail; 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InventoryAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $lowStocks,
        public array $expiringBatches
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable)
    {
        return (new InventoryAlertMail($this->lowStocks, $this->expiringBatches))
                    ->to($notifiable->email); 
    }

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