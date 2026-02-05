<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = \App\Models\Warehouse::all();
        $batches = \App\Models\ProductBatch::all();

        foreach ($warehouses as $wh) {
            foreach ($batches as $batch) {
                \App\Models\Stock::create([
                    'warehouse_id' => $wh->id,
                    'product_batch_id' => $batch->id,
                    'quantity' => rand(10, 100),
                ]);
            }
        }
    }
}
