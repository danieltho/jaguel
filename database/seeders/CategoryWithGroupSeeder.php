<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Database\Seeder;

class CategoryWithGroupSeeder extends Seeder
{
    public function run(): void
    {
        $mates = CategoryGroup::where('slug', 'mates')->first();
        $cuchillos = CategoryGroup::where('slug', 'cuchillos')->first();
        $accesorios = CategoryGroup::where('slug', 'accesorios')->first();

        $categories = [
            // Mates
            ['name' => 'Mates', 'slug' => 'mates', 'category_group_id' => $mates->id],
            ['name' => 'Bombillas', 'slug' => 'bombillas', 'category_group_id' => $mates->id],
            ['name' => 'Yerberas', 'slug' => 'yerberas', 'category_group_id' => $mates->id],
            ['name' => 'Termos', 'slug' => 'termos', 'category_group_id' => $mates->id],
            ['name' => 'Materas', 'slug' => 'materas', 'category_group_id' => $mates->id],
            ['name' => 'Sets de Mate', 'slug' => 'sets-de-mate', 'category_group_id' => $mates->id],

            // Cuchillos
            ['name' => 'Cuchillos Criollos', 'slug' => 'cuchillos-criollos', 'category_group_id' => $cuchillos->id],
            ['name' => 'Cuchillos Damasco', 'slug' => 'cuchillos-damasco', 'category_group_id' => $cuchillos->id],
            ['name' => 'Facones', 'slug' => 'facones', 'category_group_id' => $cuchillos->id],
            ['name' => 'Vainas', 'slug' => 'vainas', 'category_group_id' => $cuchillos->id],

            // Accesorios
            ['name' => 'Llaveros', 'slug' => 'llaveros', 'category_group_id' => $accesorios->id],
            ['name' => 'Rastras', 'slug' => 'rastras', 'category_group_id' => $accesorios->id],
            ['name' => 'Hebillas', 'slug' => 'hebillas', 'category_group_id' => $accesorios->id],
            ['name' => 'Indumentaria', 'slug' => 'indumentaria', 'category_group_id' => $accesorios->id],
            ['name' => 'Boinas', 'slug' => 'boinas', 'category_group_id' => $accesorios->id],
            ['name' => 'Collares y Pulseras', 'slug' => 'collares-y-pulseras', 'category_group_id' => $accesorios->id],
            ['name' => 'Tirador Bordado', 'slug' => 'tirador-bordado', 'category_group_id' => $accesorios->id],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
