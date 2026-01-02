<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Tag::factory()->count(10)->create();
        Product::factory()->count(20)->create();
        ProductCategory::factory()->count(5)->create();
        ProductCategory::factory()->child()->count(3)->create();
    }
}
