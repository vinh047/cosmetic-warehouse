<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_code' => 'ORD-' . strtoupper(fake()->unique()->alphanumeric(8)),
            'user_id' => \App\Models\User::all()->random()->id,
            'channel' => fake()->randomElement(['online', 'offline']),
            'customer_name' => fake()->name(),
            'total_price' => 0, // Sẽ tính toán dựa trên OrderItem sau
            'status' => 'completed',
        ];
    }
}
