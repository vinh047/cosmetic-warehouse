<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductAlert;

class ProductAlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tất cả các ID sản phẩm hiện có trong bảng products
        $productIds = Product::pluck('id');

        if ($productIds->isEmpty()) {
            $this->command->info('Không có sản phẩm nào trong DB. Vui lòng seed Product trước!');
            return;
        }

        $alerts = [];
        $now = now();

        foreach ($productIds as $id) {
            // Chuẩn bị mảng dữ liệu để insert (hoặc update)
            $alerts[] = [
                'product_id' => $id,
                'stock_threshold' => 300, // Bạn có thể dùng rand(10, 30) nếu muốn random dữ liệu test
                'expiry_threshold_days' => 200000,
                'last_stock_alert_at' => null,
                'last_expiry_alert_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Sử dụng upsert để chèn dữ liệu nhanh (bulk insert). 
        // Nếu product_id đã tồn tại, nó sẽ không báo lỗi mà tiến hành cập nhật.
        ProductAlert::upsert(
            $alerts, 
            ['product_id'], // Cột dùng để check trùng lặp (Unique key)
            ['stock_threshold', 'expiry_threshold_days', 'updated_at'] // Các cột sẽ update nếu bị trùng
        );

        $this->command->info('Đã seed thành công cấu hình cảnh báo cho ' . count($alerts) . ' sản phẩm!');
    }
}