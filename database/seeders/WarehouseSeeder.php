<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Warehouse::create(['name' => 'Kho Tổng TP.HCM', 'location' => 'Quận 1', 'is_active' => true]);
        \App\Models\Warehouse::create(['name' => 'Kho Miền Bắc', 'location' => 'Hà Nội', 'is_active' => true]);
    }
}
