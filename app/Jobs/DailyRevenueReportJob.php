<?php

namespace App\Jobs;

use App\Mail\DailyRevenueMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class DailyRevenueReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // Giới hạn thời gian chạy 2 phút

    public function handle(): void
    {
        Log::info('Bắt đầu chạy Job chốt ca doanh thu ngày...');

        $today = Carbon::today();

        try {
            // 1. Lấy tất cả đơn hàng COMPLETED trong ngày hôm nay
            $orders = Order::whereDate('created_at', $today)
                ->where('status', 'completed') // Chữ thường hoặc hoa tùy DB của bạn lưu
                ->get();

            $totalOrders = $orders->count();
            $totalRevenue = $orders->sum('total_price');

            // 2. Tính tổng số lượng sản phẩm đã bán ra trong ngày (Join bảng order_items)
            $totalProductsSold = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereDate('orders.created_at', $today)
                ->where('orders.status', 'completed')
                ->sum('order_items.quantity');

            // 3. (Tuỳ chọn rất khuyên dùng) Lưu thẳng vào bảng daily_reports đã có sẵn trong DB của bạn
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

            // 4. Tìm tất cả User là Admin hoặc Manager để gửi báo cáo
            $managers = User::whereIn('role', ['admin', 'manager'])
                ->where('is_active', true)
                ->get();

            if ($managers->isEmpty()) {
                Log::warning('Job Chốt ca: Không có Quản lý/Admin nào để gửi email.');
                return;
            }

            // 5. Gửi Email cho từng Quản lý qua Queue
            foreach ($managers as $manager) {
                Mail::to($manager->email)->send(
                    new DailyRevenueMail($today, $totalOrders, $totalRevenue, $totalProductsSold)
                );
            }

            Log::info("Job Chốt ca thành công: Doanh thu {$totalRevenue} từ {$totalOrders} đơn hàng.");

        } catch (\Exception $e) {
            Log::error('Lỗi Job Chốt ca: ' . $e->getMessage());
            throw $e;
        }
    }
}