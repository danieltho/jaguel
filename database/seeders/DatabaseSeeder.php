<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            ColorSeeder::class,
            SizeSeeder::class,
            CategoryGroupSeeder::class,
            CategoryWithGroupSeeder::class,
            ProductSeeder::class,
            TestProductSeeder::class,
        ]);
    }
}
