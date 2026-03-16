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
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GenerateExcelReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected $month;
    protected $year;
    protected $managerId;

    public function __construct($month, $year, $managerId)
    {
        $this->month = $month;
        $this->year = $year;
        $this->managerId = $managerId;
    }

    public function handle(): void
    {
        try {

            $manager = User::find($this->managerId);

            if (!$manager) {
                Log::warning("Manager not found: " . $this->managerId);
                return;
            }

            // Tạo tên file
            $fileName = "inventory_report_{$this->month}_{$this->year}_" . time() . ".xlsx";

            // Đường dẫn lưu file
            $filePath = "exports/" . $fileName;

            // Tạo file Excel
            Excel::store(
                new InventoryTransactionExport($this->month, $this->year),
                $filePath,
                'local'
            );

            Log::info("Excel file created: " . $filePath);

            // Gửi mail
            Mail::to($manager->email)
                ->send(new InventoryExportMail(
                    $filePath,
                    $fileName,
                    $this->month,
                    $this->year
                ));

            Log::info("Mail sent to: " . $manager->email);

            // Xóa file sau khi gửi
            Storage::disk('local')->delete($filePath);

        } catch (\Exception $e) {

            Log::error('Excel export job failed: ' . $e->getMessage());

            throw $e;
        }
    }
}
