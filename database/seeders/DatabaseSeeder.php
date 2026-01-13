<?php

namespace Database\Seeders;

use App\Models\Product;
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
        $this->call([
            ColorSeeder::class,
            SizeSeeder::class,
            CategoryGroupSeeder::class,
            CategoryWithGroupSeeder::class,
        ]);

        // User::factory(10)->create();
        //Product::factory()->count(3)->create();
    }
}
