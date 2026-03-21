<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InventoryExportMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public int $month,
        public int $year,
        public string $downloadUrl 
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Báo cáo giao dịch kho tháng {$this->month}/{$this->year}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reports.inventory-export',
            with: [
                'month' => $this->month,
                'year' => $this->year,
                'downloadUrl' => $this->downloadUrl 
            ]
        );
    }

}