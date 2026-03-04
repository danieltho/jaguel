<?php

namespace Database\Seeders;

use App\Models\CategoryGroup;
use Illuminate\Database\Seeder;

class CategoryGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['name' => 'Mates', 'slug' => 'mates'],
            ['name' => 'Cuchillos', 'slug' => 'cuchillos'],
            ['name' => 'Accesorios', 'slug' => 'accesorios'],
        ];

        foreach ($groups as $group) {
            CategoryGroup::create($group);
        }
    }
}
