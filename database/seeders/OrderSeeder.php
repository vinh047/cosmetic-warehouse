<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::pluck('id');
        $batches = \App\Models\ProductBatch::all();
        

        for ($i = 1; $i <= 5; $i++) {
            $order = \App\Models\Order::create([
                'order_code' => "ORD-2026-" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'user_id' => $users->random(),
                'customer_name' => "Khách hàng $i",
                'total_price' => 0,
                'status' => 'completed',
            ]);

            $total = 0;
            // Mỗi đơn hàng lấy 2 lô hàng ngẫu nhiên để bán
            $selectedBatches = $batches->random(2);
            foreach ($selectedBatches as $batch) {
                $qty = rand(1, 3);
                $price = $batch->product->price;

                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_batch_id' => $batch->id,
                    'quantity' => $qty,
                    'price' => $price,
                ]);
                $total += ($qty * $price);
            }
            $order->update(['total_price' => $total]);
        }
    }
}
