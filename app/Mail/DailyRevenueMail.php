<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class DailyRevenueMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Carbon $date,
        public int $totalOrders,
        public float $totalRevenue,
        public int $totalProductsSold
    ) {}

    /**
     * Định dạng Tiêu đề Email theo đúng yêu cầu:
     * "Báo cáo chốt ca ngày 14/03: Doanh thu 50,000,000 VNĐ"
     */
    public function envelope(): Envelope
    {
        $dateString = $this->date->format('d/m');
        $revenueString = number_format($this->totalRevenue, 0, ',', '.');

        return new Envelope(
            subject: "Báo cáo chốt ca ngày {$dateString}: Doanh thu {$revenueString} VNĐ",
        );
    }

    /**
     * Chỉ định View chứa giao diện HTML
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reports.daily-revenue',
        );
    }
}