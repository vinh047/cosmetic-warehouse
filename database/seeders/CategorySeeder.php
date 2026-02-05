<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Điện thoại', 'description' => 'Smartphone và phụ kiện'],
            ['name' => 'Laptop', 'description' => 'Máy tính xách tay văn phòng, gaming'],
            ['name' => 'Linh kiện', 'description' => 'RAM, CPU, VGA'],
        ];
        foreach ($data as $item) \App\Models\Category::create($item);
    }
}
