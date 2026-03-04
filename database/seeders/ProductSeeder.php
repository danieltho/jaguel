<?php

namespace Database\Seeders;

use App\Enums\ProductTypeEnum;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // --- Mates ---
            [
                'name' => 'Mate de Algarrobo con Virolas de Alpaca',
                'description' => 'Mate artesanal de algarrobo con virolas y bombilla de alpaca. Curado y listo para usar.',
                'category_slug' => 'mates',
                'price_sold' => 5056600,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Mate Imperial de Caldén con Pie de Alpaca',
                'description' => 'Mate imperial tallado en caldén con base y virolas de alpaca labrada.',
                'category_slug' => 'mates',
                'price_sold' => 7850000,
                'price_sales' => 6500000,
                'is_active' => true,
            ],
            [
                'name' => 'Mate Camionero de Madera con Revestimiento de Cuero',
                'description' => 'Mate camionero con revestimiento de cuero crudo trenzado y virola de alpaca.',
                'category_slug' => 'mates',
                'price_sold' => 3200000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Mate Torpedo de Palo Santo',
                'description' => 'Mate torpedo confeccionado en palo santo natural con aroma característico.',
                'category_slug' => 'mates',
                'price_sold' => 4500000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Bombillas ---
            [
                'name' => 'Bombilla Coquito Pico de Loro de Alpaca «Yaguareté»',
                'description' => 'Bombilla de alpaca tipo coquito pico de loro con grabado de yaguareté.',
                'category_slug' => 'bombillas',
                'price_sold' => 4227700,
                'price_sales' => 3843400,
                'is_active' => true,
            ],
            [
                'name' => 'Bombilla de Alpaca con Resorte y Paleta',
                'description' => 'Bombilla de alpaca con filtro de resorte y paleta, ideal para yerba fina.',
                'category_slug' => 'bombillas',
                'price_sold' => 2800000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Yerberas ---
            [
                'name' => 'Yerbera de Cuero Repujado con Tapa de Alpaca',
                'description' => 'Yerbera artesanal de cuero repujado con motivos criollos y tapa de alpaca.',
                'category_slug' => 'yerberas',
                'price_sold' => 3600000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Cuchillos ---
            [
                'name' => 'Cuchillo de Acero Damasco',
                'description' => 'Cuchillo artesanal con hoja de acero Damasco de 200 capas y mango de guayacán.',
                'category_slug' => 'cuchillos-damasco',
                'price_sold' => 18727200,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Cuchillo Criollo con Cabo de Asta de Ciervo',
                'description' => 'Cuchillo criollo con hoja de acero inoxidable y cabo de asta de ciervo natural.',
                'category_slug' => 'cuchillos-criollos',
                'price_sold' => 12500000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Facón de Alpaca con Vaina de Cuero',
                'description' => 'Facón de gala con guarda de alpaca labrada y vaina de cuero crudo con punteras.',
                'category_slug' => 'facones',
                'price_sold' => 25000000,
                'price_sales' => 22000000,
                'is_active' => true,
            ],
            [
                'name' => 'Cuchillo de Acero al Carbono con Mango de Madera',
                'description' => 'Cuchillo criollo de acero al carbono con mango de quebracho y virola de bronce.',
                'category_slug' => 'cuchillos-criollos',
                'price_sold' => 8900000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Accesorios / Llaveros ---
            [
                'name' => 'Llavero de Cuero Crudo con Dije de Alpaca',
                'description' => 'Llavero artesanal de cuero crudo trenzado con dije de alpaca con motivo gaucho.',
                'category_slug' => 'llaveros',
                'price_sold' => 1951400,
                'price_sales' => 1626200,
                'is_active' => true,
            ],
            [
                'name' => 'Llavero Trenzado de Cuero con Herraje',
                'description' => 'Llavero de cuero trenzado a mano con herraje de alpaca.',
                'category_slug' => 'llaveros',
                'price_sold' => 1500000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Rastras ---
            [
                'name' => 'Rastra de Alpaca con Monedas Antiguas',
                'description' => 'Rastra de gala con cadenas de alpaca y monedas antiguas originales.',
                'category_slug' => 'rastras',
                'price_sold' => 35000000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Indumentaria ---
            [
                'name' => 'Chaleco Artesanal Bordado con Guardas',
                'description' => 'Chaleco artesanal con bordado de guardas pampeanas en hilo de algodón.',
                'category_slug' => 'indumentaria',
                'price_sold' => 9800000,
                'price_sales' => 8500000,
                'is_active' => true,
            ],

            // --- Boinas ---
            [
                'name' => 'Boina de Paño',
                'description' => 'Boina clásica de paño en color negro, ideal para el campo y la tradición.',
                'category_slug' => 'boinas',
                'price_sold' => 2500000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Collares y Pulseras ---
            [
                'name' => 'Collar Rígido de Alpaca',
                'description' => 'Collar rígido de alpaca con diseño geométrico inspirado en la cultura mapuche.',
                'category_slug' => 'collares-y-pulseras',
                'price_sold' => 4800000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Pulsera de Cuero con Pasador de Alpaca',
                'description' => 'Pulsera de cuero curtido con pasador de alpaca grabado.',
                'category_slug' => 'collares-y-pulseras',
                'price_sold' => 1800000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Tirador Bordado ---
            [
                'name' => 'Tirador Bordado con Rastra de Alpaca',
                'description' => 'Tirador de cuero con bordado artesanal y rastra de alpaca con monedas.',
                'category_slug' => 'tirador-bordado',
                'price_sold' => 28000000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Más productos para completar el grid ---
            [
                'name' => 'Mate de Calabaza con Virola de Plata',
                'description' => 'Mate de calabaza natural con virola de plata 925 y grabado artesanal.',
                'category_slug' => 'mates',
                'price_sold' => 6200000,
                'price_sales' => 5800000,
                'is_active' => true,
            ],
            [
                'name' => 'Vaina de Cuero Crudo para Cuchillo',
                'description' => 'Vaina artesanal de cuero crudo con costura a mano y punteras de alpaca.',
                'category_slug' => 'vainas',
                'price_sold' => 3500000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Hebilla Ovalada de Alpaca con Flor de Cardo',
                'description' => 'Hebilla ovalada de alpaca con diseño de flor de cardo en relieve.',
                'category_slug' => 'hebillas',
                'price_sold' => 5500000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Bombilla de Acero Inoxidable con Filtro',
                'description' => 'Bombilla de acero inoxidable con filtro desmontable y mango torneado.',
                'category_slug' => 'bombillas',
                'price_sold' => 1200000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Mate Artesanal de Vidrio con Funda de Cuero',
                'description' => 'Mate de vidrio térmico con funda de cuero cosida a mano.',
                'category_slug' => 'mates',
                'price_sold' => 3800000,
                'price_sales' => 3200000,
                'is_active' => true,
            ],
        ];

        foreach ($products as $data) {
            $categorySlug = $data['category_slug'];
            unset($data['category_slug']);

            $category = Category::where('slug', $categorySlug)->first();
            $data['slug'] = Str::slug($data['name']);
            $data['category_id'] = $category?->id;
            $data['type'] = ProductTypeEnum::FISICO;
            $data['price_provider'] = (int) ($data['price_sold'] * 0.5);
            $data['price_cost'] = (int) ($data['price_sold'] * 0.6);

            Product::create($data);
        }
    }
}
