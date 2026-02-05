<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = \App\Models\Category::pluck('id');
        $brands = \App\Models\Brand::pluck('id');

        for ($i = 1; $i <= 10; $i++) {
            \App\Models\Product::create([
                'name' => "Sản phẩm mã số $i",
                'sku' => "SKU-" . str_pad($i, 5, '0', STR_PAD_LEFT),
                'category_id' => $categories->random(),
                'brand_id' => $brands->random(),
                'price' => rand(100, 2000) * 1000,
                'is_active' => true,
            ]);
        }
    }
}
