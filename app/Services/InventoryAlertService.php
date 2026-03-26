<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAlert;
use App\Models\User;
use App\Notifications\InventoryAlertNotification; // <-- Đổi sang dùng Notification
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // <-- Dùng Facade Notification
use Carbon\Carbon;

class InventoryAlertService
{
    /**
     * Dùng cho EVENT: Kiểm tra Real-time ngay khi có đơn hàng
     */
    public function checkRealtimeLowStock($productIds)
    {
        $productIds = array_unique($productIds);
        $alertsToSend = [];

        // TỐI ƯU HÓA: Lấy tất cả product trong 1 câu query thay vì find() trong vòng lặp foreach
        $products = Product::with('alert')->whereIn('id', $productIds)->get();

        foreach ($products as $product) {
            if (!$product->alert) continue;

            $lastAlert = $product->alert->last_stock_alert_at;

            // Tránh spam: Chỉ kiểm tra và gửi nếu chưa gửi hoặc đã qua 24 tiếng
            if (!$lastAlert || Carbon::parse($lastAlert)->diffInHours(now()) >= 24) {

                // Tính tổng tồn theo TỪNG KHO
                $stocksPerWarehouse = DB::table('stocks')
                    ->join('product_batches', 'stocks.product_batch_id', '=', 'product_batches.id')
                    ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
                    ->where('product_batches.product_id', $product->id)
                    ->select('warehouses.name as warehouse_name', DB::raw('SUM(stocks.quantity) as total_qty'))
                    ->groupBy('stocks.warehouse_id', 'warehouses.name')
                    ->get();

                $hasLowStock = false;

                // Trường hợp 1: Sản phẩm chưa từng có record trong kho (Tổng tồn = 0 toàn hệ thống)
                if ($stocksPerWarehouse->isEmpty()) {
                    if (0 <= $product->alert->stock_threshold) {
                        $alertsToSend[] = (object)[
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'warehouse_name' => 'Chưa có dữ liệu kho (Tồn = 0)',
                            'total_qty' => 0,
                            'stock_threshold' => $product->alert->stock_threshold
                        ];
                        $hasLowStock = true;
                    }
                }
                // Trường hợp 2: Quét từng kho xem kho nào dưới mức cảnh báo
                else {
                    foreach ($stocksPerWarehouse as $stock) {
                        if ($stock->total_qty <= $product->alert->stock_threshold) {
                            $alertsToSend[] = (object)[
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'warehouse_name' => $stock->warehouse_name,
                                'total_qty' => $stock->total_qty,
                                'stock_threshold' => $product->alert->stock_threshold
                            ];
                            $hasLowStock = true;
                        }
                    }
                }

                // Nếu có ít nhất 1 kho báo động, cập nhật lại thời gian để ngắt spam
                if ($hasLowStock) {
                    $product->alert->update(['last_stock_alert_at' => now()]);
                }
            }
        }

        if (count($alertsToSend) > 0) {
            $this->sendNotification($alertsToSend, []); // Đổi tên hàm gọi
        }
    }

    /**
     * Dùng cho CRON JOB: Quét toàn hệ thống hằng ngày (Kiểm tra Date + Quét vét Tồn kho)
     */
    public function checkDailyAlerts()
    {
        // ==========================================
        // 1. QUÉT HÀNG CẬN DATE
        // ==========================================
        $expiringBatches = DB::select("
            SELECT p.id as product_id, p.name, p.sku, pb.batch_code, pb.expiry_date, s.quantity, pa.last_expiry_alert_at, pa.expiry_threshold_days
            FROM product_batches pb
            JOIN products p ON pb.product_id = p.id
            JOIN product_alerts pa ON p.id = pa.product_id
            JOIN stocks s ON pb.id = s.product_batch_id
            WHERE pb.is_active = 1 AND s.quantity > 0
            AND pb.expiry_date <= DATE_ADD(CURDATE(), INTERVAL pa.expiry_threshold_days DAY)
        ");

        $expiringToSend = [];
        $expiringProductIdsToUpdate = [];

        foreach ($expiringBatches as $batch) {
            $lastAlert = $batch->last_expiry_alert_at ? Carbon::parse($batch->last_expiry_alert_at) : null;
            if (!$lastAlert || $lastAlert->diffInDays(now()) >= 7) {
                $expiringToSend[] = $batch;
                $expiringProductIdsToUpdate[] = $batch->product_id;
            }
        }

        // ==========================================
        // 2. QUÉT VÉT HÀNG SẮP HẾT (LOW STOCK)
        // ==========================================
        $lowStocks = DB::select("
            SELECT p.id as product_id, p.name, p.sku, pa.stock_threshold, pa.last_stock_alert_at, 
                   COALESCE(w.name, 'Chưa có dữ liệu kho') as warehouse_name, 
                   COALESCE(SUM(s.quantity), 0) as total_qty
            FROM products p
            JOIN product_alerts pa ON p.id = pa.product_id
            LEFT JOIN product_batches pb ON p.id = pb.product_id
            LEFT JOIN stocks s ON pb.id = s.product_batch_id
            LEFT JOIN warehouses w ON s.warehouse_id = w.id
            WHERE p.is_active = 1
            GROUP BY p.id, p.name, p.sku, pa.stock_threshold, pa.last_stock_alert_at, w.id, w.name
            HAVING total_qty <= pa.stock_threshold
        ");

        $lowStocksToSend = [];
        $lowStockProductIdsToUpdate = [];

        foreach ($lowStocks as $stock) {
            $lastAlert = $stock->last_stock_alert_at ? Carbon::parse($stock->last_stock_alert_at) : null;
            if (!$lastAlert || $lastAlert->diffInHours(now()) >= 24) {
                $lowStocksToSend[] = $stock;
                $lowStockProductIdsToUpdate[] = $stock->product_id;
            }
        }

        // ==========================================
        // 3. TỔNG HỢP VÀ GỬI NOTIFICATION
        // ==========================================
        if (count($expiringToSend) > 0 || count($lowStocksToSend) > 0) {

            $this->sendNotification($lowStocksToSend, $expiringToSend); // Đổi tên hàm gọi

            if (count($expiringProductIdsToUpdate) > 0) {
                ProductAlert::whereIn('product_id', array_unique($expiringProductIdsToUpdate))
                    ->update(['last_expiry_alert_at' => now()]);
            }

            if (count($lowStockProductIdsToUpdate) > 0) {
                ProductAlert::whereIn('product_id', array_unique($lowStockProductIdsToUpdate))
                    ->update(['last_stock_alert_at' => now()]);
            }
        }
    }

    /**
     * Hàm gửi thông báo (thay thế cho sendEmail cũ)
     */
    private function sendNotification($lowStocks, $expiringBatches)
    {
        try {
            // Lấy ra Collection các user thay vì chỉ lấy mảng email
            $managers = User::whereIn('role', ['admin', 'manager'])
                ->where('is_active', true)
                ->get();

            if ($managers->isNotEmpty()) {
                // Laravel tự động duyệt qua các user và gửi thông báo
                Notification::send($managers, new InventoryAlertNotification($lowStocks, $expiringBatches));
            } else {
                Log::warning('InventoryAlert: Không có tài khoản admin/manager nào để nhận thông báo.');
            }
        } catch (\Exception $e) {
            Log::error('Lỗi gửi cảnh báo kho: ' . $e->getMessage());
        }
    }
}
