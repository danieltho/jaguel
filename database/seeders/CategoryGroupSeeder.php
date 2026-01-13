<?php

namespace Database\Seeders;

use App\Models\CategoryGroup;
use Illuminate\Database\Seeder;

class CategoryGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryGroups = ['Mates', 'Cuchillos', 'Accesorios'];

        foreach ($categoryGroups as $group) {
            CategoryGroup::create(['name' => $group]);
        }
    }
}
