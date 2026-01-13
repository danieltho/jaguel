<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = ['XS', 'M', 'L', 'XL', 'XXL'];

        foreach ($sizes as $size) {
            Size::create(['name' => $size]);
        }
    }
}
