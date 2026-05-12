<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
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
