<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InventoryExportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public string $fileName,
        public int $month,
        public int $year
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
                'year' => $this->year
            ]
        );
    }

    public function attachments(): array
    {
        return [

            Attachment::fromStorageDisk('local', $this->filePath)
                ->as($this->fileName)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')

        ];
    }
}
