<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAlert;
use App\Models\User;
use App\Mail\InventoryAlertMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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

        foreach ($productIds as $productId) {
            $product = Product::with('alert')->find($productId);
            
            if (!$product || !$product->alert) continue;

            $totalStock = DB::table('stocks')
                ->join('product_batches', 'stocks.product_batch_id', '=', 'product_batches.id')
                ->where('product_batches.product_id', $productId)
                ->sum('stocks.quantity');

            if ($totalStock <= $product->alert->stock_threshold) {
                $lastAlert = $product->alert->last_stock_alert_at;
                
                // Tránh spam: Chỉ gửi nếu chưa gửi hoặc đã qua 24 tiếng
                if (!$lastAlert || $lastAlert->diffInHours(now()) >= 24) {
                    $alertsToSend[] = (object)[
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'total_qty' => $totalStock,
                        'stock_threshold' => $product->alert->stock_threshold
                    ];

                    $product->alert->update(['last_stock_alert_at' => now()]);
                }
            }
        }

        if (count($alertsToSend) > 0) {
            $this->sendEmail($alertsToSend, []);
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
            // Báo cận date 7 ngày 1 lần
            if (!$lastAlert || $lastAlert->diffInDays(now()) >= 7) {
                $expiringToSend[] = $batch;
                $expiringProductIdsToUpdate[] = $batch->product_id;
            }
        }

        // ==========================================
        // 2. QUÉT VÉT HÀNG SẮP HẾT (LOW STOCK)
        // ==========================================
        // Lưu ý: Dùng LEFT JOIN để bắt được cả những sản phẩm có tồn kho bằng 0 (không có trong bảng stocks)
        $lowStocks = DB::select("
            SELECT p.id as product_id, p.name, p.sku, pa.stock_threshold, pa.last_stock_alert_at, COALESCE(SUM(s.quantity), 0) as total_qty
            FROM products p
            JOIN product_alerts pa ON p.id = pa.product_id
            LEFT JOIN product_batches pb ON p.id = pb.product_id
            LEFT JOIN stocks s ON pb.id = s.product_batch_id
            WHERE p.is_active = 1
            GROUP BY p.id, p.name, p.sku, pa.stock_threshold, pa.last_stock_alert_at
            HAVING total_qty <= pa.stock_threshold
        ");

        $lowStocksToSend = [];
        $lowStockProductIdsToUpdate = [];

        foreach ($lowStocks as $stock) {
            $lastAlert = $stock->last_stock_alert_at ? Carbon::parse($stock->last_stock_alert_at) : null;
            // Báo sắp hết hàng 24h 1 lần
            if (!$lastAlert || $lastAlert->diffInHours(now()) >= 24) {
                $lowStocksToSend[] = $stock;
                $lowStockProductIdsToUpdate[] = $stock->product_id;
            }
        }

        // ==========================================
        // 3. TỔNG HỢP VÀ GỬI EMAIL CHUNG
        // ==========================================
        if (count($expiringToSend) > 0 || count($lowStocksToSend) > 0) {
            
            $this->sendEmail($lowStocksToSend, $expiringToSend);
            
            // Cập nhật lại thời gian đã gửi mail để chặn spam cho lần quét ngày mai
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
     * Hàm gửi mail chung
     */
    private function sendEmail($lowStocks, $expiringBatches)
    {
        try {
            $managers = User::whereIn('role', ['admin', 'manager'])
                ->where('is_active', true)
                ->pluck('email')
                ->toArray();
                
            if (!empty($managers)) {
                Mail::to($managers)->send(new InventoryAlertMail($lowStocks, $expiringBatches));
            } else {
                Log::warning('InventoryAlert: Không có tài khoản admin/manager nào để gửi mail báo cáo.');
            }
        } catch (\Exception $e) {
            Log::error('Lỗi gửi mail cảnh báo kho: ' . $e->getMessage());
        }
    }
}