<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\Stock;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Seeder;

class InventoryTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lấy user admin để gán cho các giao dịch hệ thống
        $adminId = User::where('role', 'admin')->first()?->id ?? User::first()->id;

        // 2. TẠO LỊCH SỬ NHẬP KHO (IN)
        // Logic: Mỗi bản ghi trong bảng Stock thực chất là kết quả của một lần nhập kho ban đầu
        $stocks = Stock::all();
        foreach ($stocks as $stock) {
            InventoryTransaction::create([
                'user_id' => $adminId,
                'product_batch_id' => $stock->product_batch_id,
                'warehouse_id' => $stock->warehouse_id,
                'type' => 'IN',
                'quantity' => $stock->quantity, // Khớp với số lượng đang có trong StockSeeder
                'reference_type' => 'Initial Seed',
                'reference_id' => null,
                'created_at' => now()->subDays(10), // Giả định nhập cách đây 10 ngày
            ]);
        }

        // 3. TẠO LỊCH SỬ XUẤT KHO (OUT) TỪ ĐƠN HÀNG
        // Logic: Mỗi món hàng trong OrderItem cần một dòng giao dịch OUT tương ứng
        $orders = Order::with('items.productBatch')->get();
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                // Giả định xuất từ kho đầu tiên (Kho Tổng TP.HCM)
                InventoryTransaction::create([
                    'user_id' => $order->user_id,
                    'product_batch_id' => $item->product_batch_id,
                    'warehouse_id' => 1, // ID của kho Tổng
                    'type' => 'OUT',
                    'quantity' => $item->quantity,
                    'reference_type' => 'Order',
                    'reference_id' => $order->id,
                    'created_at' => $order->created_at,
                ]);
            }
        }

        $this->command->info('Da tao xong lich su giao dich kho (IN/OUT).');
    }
}