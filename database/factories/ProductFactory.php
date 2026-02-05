<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->productName, // Cần cài thêm thư viện faker-commerce hoặc dùng word
            'sku' => strtoupper(fake()->unique()->bothify('PROD-####-????')),
            'category_id' => \App\Models\Category::all()->random()->id,
            'brand_id' => \App\Models\Brand::all()->random()->id,
            'price' => fake()->randomFloat(2, 10, 1000),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
