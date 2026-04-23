<?php

namespace Database\Seeders;

use App\Models\CategoryGroup;
use Illuminate\Database\Seeder;

class CategoryGroupSeeder extends Seeder
{
    private const ASSETS_PATH = 'seeders/assets/category-groups';

    public function run(): void
    {
        $groups = [
            ['name' => 'Mates', 'slug' => 'mates', 'image' => 'mates.jpg'],
            ['name' => 'Cuchillos', 'slug' => 'cuchillos', 'image' => 'cuchillos.jpg'],
            ['name' => 'Accesorios', 'slug' => 'accesorios', 'image' => 'accesorios.jpg'],
        ];

        foreach ($groups as $data) {
            $imageFile = $data['image'];
            unset($data['image']);

            $group = CategoryGroup::create($data);

            $this->attachImage($group, $imageFile);
        }
    }

    private function attachImage(CategoryGroup $group, string $filename): void
    {
        $path = database_path(self::ASSETS_PATH . '/' . $filename);

        if (! file_exists($path)) {
            $this->command?->warn("Imagen no encontrada: {$path}");
            return;
        }

        $group
            ->addMedia($path)
            ->preservingOriginal()
            ->toMediaCollection('default');
    }
}
