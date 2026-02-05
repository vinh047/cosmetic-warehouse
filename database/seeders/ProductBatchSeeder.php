<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductBatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = \App\Models\Product::all();
        foreach ($products as $product) {
            for ($j = 1; $j <= 2; $j++) {
                \App\Models\ProductBatch::create([
                    'product_id' => $product->id,
                    'batch_code' => "LOT-{$product->id}-00{$j}",
                    'manufacture_date' => now()->subMonths(rand(1, 12)),
                    'expiry_date' => now()->addMonths(rand(6, 24)),
                    'is_active' => true,
                ]);
            }
        }
    }
}
