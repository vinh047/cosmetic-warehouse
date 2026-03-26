<?php

namespace App\Jobs;

use App\Exports\InventoryTransactionExport;
use App\Notifications\InventoryExportNotification; 
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

            // 1. Tạo tên file duy nhất
            $timestamp = time();
            $fileName = "inventory_report_{$this->month}_{$this->year}_{$timestamp}.xlsx";
            $filePath = "reports/{$fileName}";

            // 2. Lưu file Excel vào storage
            Excel::store(new InventoryTransactionExport($this->month, $this->year), $filePath, 'local');

            // 3. GỌI NOTIFICATION thay vì Mail
            $manager->notify(new InventoryExportNotification(
                $filePath,
                $fileName,
                $this->month,
                $this->year
            ));

            Log::info("Job Excel: Đã tạo notification và gửi báo cáo đính kèm thành công tới {$manager->email}");

            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }
        } catch (\Exception $e) {
            Log::error("Job Excel Thất bại: " . $e->getMessage());
            throw $e;
        }
    }
}