<?php

namespace App\Jobs;

use App\Exports\InventoryTransactionExport;
use App\Mail\InventoryExportMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;

class GenerateExcelReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    public function __construct(
        protected int $month,
        protected int $year,
        protected int $managerId
    ) {}

    public function handle(): void
    {
        $manager = User::find($this->managerId);
        
        if (!$manager) {
            Log::warning("Job Excel: Không tìm thấy quản lý ID {$this->managerId}");
            return;
        }

        try {
            Log::info("Job Excel: Bắt đầu xử lý dữ liệu cho {$manager->email}");

            // 1. Tạo tên file duy nhất (tránh trùng lặp nếu xuất nhiều lần)
            $timestamp = time();
            $fileName = "inventory_report_{$this->month}_{$this->year}_{$timestamp}.xlsx";
            $filePath = "reports/{$fileName}";

            // 2. Lưu file Excel vào thư mục storage/app/reports
            Excel::store(new InventoryTransactionExport($this->month, $this->year), $filePath, 'local');

            // 3. Tạo link tải file có mã hóa (sống trong 7 ngày)
            $downloadUrl = URL::temporarySignedRoute(
                'report.download', 
                now()->addDays(7), // Link hết hạn sau 7 ngày
                ['fileName' => $fileName]
            );

            // 4. Gửi Mail chứa link tải
            Mail::to($manager->email)->send(new InventoryExportMail(
                $this->month, 
                $this->year,
                $downloadUrl // Truyền link vào mail
            ));

            Log::info("Job Excel: Đã gửi link báo cáo thành công tới {$manager->email}");

        } catch (\Exception $e) {
            Log::error("Job Excel Thất bại: " . $e->getMessage());
            throw $e;
        }
    }
}