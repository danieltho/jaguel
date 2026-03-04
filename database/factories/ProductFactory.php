<?php

namespace Database\Factories;

use App\Enums\ProductTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $priceSold = fake()->numberBetween(500000, 30000000);
        $name = fake()->words(4, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->randomNumber(5),
            'description' => fake()->paragraph(),
            'type' => ProductTypeEnum::FISICO,
            'price_sold' => $priceSold,
            'price_sales' => 0,
            'price_provider' => (int) ($priceSold * 0.5),
            'price_cost' => (int) ($priceSold * 0.6),
            'is_active' => true,
        ];
    }
}
