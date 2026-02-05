<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Apple', 'country' => 'USA'],
            ['name' => 'Samsung', 'country' => 'Korea'],
            ['name' => 'ASUS', 'country' => 'Taiwan'],
        ];
        foreach ($data as $item) \App\Models\Brand::create($item);
    }
}
