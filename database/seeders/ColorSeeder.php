<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            [
                'name' => 'Azul',
                'rgb_color' => '#0000FF',
            ],
            [
                'name' => 'Negro',
                'rgb_color' => '#000000',
            ],
            [
                'name' => 'Amarillo',
                'rgb_color' => '#FFFF00',
            ],
        ];

        foreach ($colors as $color) {
            Color::create($color);
        }
    }
}
