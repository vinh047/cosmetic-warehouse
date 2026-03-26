<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Notifications\DailyRevenueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DailyRevenueReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    public function handle(): void
    {
        Log::info('Bắt đầu chạy Job chốt ca doanh thu ngày...');
        $today = Carbon::today();

        try {
            // 1. Tính toán dữ liệu (Giữ nguyên logic của bạn)
            $orders = Order::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->get();

            $totalOrders = $orders->count();
            $totalRevenue = $orders->sum('total_price');

            $totalProductsSold = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereDate('orders.created_at', $today)
                ->where('orders.status', 'completed')
                ->sum('order_items.quantity');

            // 2. Lưu vào bảng daily_reports
            DB::table('daily_reports')->updateOrInsert(
                ['report_date' => $today->toDateString()],
                [
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue,
                    'total_products_sold' => (int) $totalProductsSold,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // 3. Lấy danh sách quản lý
            $managers = User::whereIn('role', ['admin', 'manager'])
                ->where('is_active', true)
                ->get();

            if ($managers->isEmpty()) {
                Log::warning('Job Chốt ca: Không có Quản lý nào để gửi thông báo.');
                return;
            }

            // 4. GỬI NOTIFICATION (Thay vì gửi Mail trực tiếp)
            foreach ($managers as $manager) {
                $manager->notify(new DailyRevenueNotification(
                    $today, 
                    $totalOrders, 
                    $totalRevenue, 
                    (int) $totalProductsSold
                ));
            }

            Log::info("Job Chốt ca thành công: Doanh thu {$totalRevenue} đã gửi tới các quản lý.");

        } catch (\Exception $e) {
            Log::error('Lỗi Job Chốt ca: ' . $e->getMessage());
            throw $e;
        }
    }
}