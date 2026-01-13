<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Database\Seeder;

class CategoryWithGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener los grupos de categorías
        $mates = CategoryGroup::where('name', 'Mates')->first();
        $accesorios = CategoryGroup::where('name', 'Accesorios')->first();

        // Crear categorías con sus respectivos grupos
        $categories = [
            [
                'name' => 'Mates',
                'category_group_id' => $mates->id,
            ],
            [
                'name' => 'Bombillas',
                'category_group_id' => $mates->id,
            ],
            [
                'name' => 'Indumentaria',
                'category_group_id' => $accesorios->id,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
