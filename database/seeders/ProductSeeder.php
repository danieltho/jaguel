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
                'price_sold' => 50566,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Mate Imperial de Caldén con Pie de Alpaca',
                'description' => 'Mate imperial tallado en caldén con base y virolas de alpaca labrada.',
                'category_slug' => 'mates',
                'price_sold' => 78500,
                'price_sales' => 65000,
                'is_active' => true,
            ],
            [
                'name' => 'Mate Camionero de Madera con Revestimiento de Cuero',
                'description' => 'Mate camionero con revestimiento de cuero crudo trenzado y virola de alpaca.',
                'category_slug' => 'mates',
                'price_sold' => 32000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Mate Torpedo de Palo Santo',
                'description' => 'Mate torpedo confeccionado en palo santo natural con aroma característico.',
                'category_slug' => 'mates',
                'price_sold' => 45000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Bombillas ---
            [
                'name' => 'Bombilla Coquito Pico de Loro de Alpaca «Yaguareté»',
                'description' => 'Bombilla de alpaca tipo coquito pico de loro con grabado de yaguareté.',
                'category_slug' => 'bombillas',
                'price_sold' => 42277,
                'price_sales' => 38434,
                'is_active' => true,
            ],
            [
                'name' => 'Bombilla de Alpaca con Resorte y Paleta',
                'description' => 'Bombilla de alpaca con filtro de resorte y paleta, ideal para yerba fina.',
                'category_slug' => 'bombillas',
                'price_sold' => 28000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Yerberas ---
            [
                'name' => 'Yerbera de Cuero Repujado con Tapa de Alpaca',
                'description' => 'Yerbera artesanal de cuero repujado con motivos criollos y tapa de alpaca.',
                'category_slug' => 'yerberas',
                'price_sold' => 36000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Cuchillos ---
            [
                'name' => 'Cuchillo de Acero Damasco',
                'description' => 'Cuchillo artesanal con hoja de acero Damasco de 200 capas y mango de guayacán.',
                'category_slug' => 'cuchillos-damasco',
                'price_sold' => 187272,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Cuchillo Criollo con Cabo de Asta de Ciervo',
                'description' => 'Cuchillo criollo con hoja de acero inoxidable y cabo de asta de ciervo natural.',
                'category_slug' => 'cuchillos-criollos',
                'price_sold' => 125000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Facón de Alpaca con Vaina de Cuero',
                'description' => 'Facón de gala con guarda de alpaca labrada y vaina de cuero crudo con punteras.',
                'category_slug' => 'facones',
                'price_sold' => 250000,
                'price_sales' => 220000,
                'is_active' => true,
            ],
            [
                'name' => 'Cuchillo de Acero al Carbono con Mango de Madera',
                'description' => 'Cuchillo criollo de acero al carbono con mango de quebracho y virola de bronce.',
                'category_slug' => 'cuchillos-criollos',
                'price_sold' => 89000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Accesorios / Llaveros ---
            [
                'name' => 'Llavero de Cuero Crudo con Dije de Alpaca',
                'description' => 'Llavero artesanal de cuero crudo trenzado con dije de alpaca con motivo gaucho.',
                'category_slug' => 'llaveros',
                'price_sold' => 19514,
                'price_sales' => 16262,
                'is_active' => true,
            ],
            [
                'name' => 'Llavero Trenzado de Cuero con Herraje',
                'description' => 'Llavero de cuero trenzado a mano con herraje de alpaca.',
                'category_slug' => 'llaveros',
                'price_sold' => 15000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Rastras ---
            [
                'name' => 'Rastra de Alpaca con Monedas Antiguas',
                'description' => 'Rastra de gala con cadenas de alpaca y monedas antiguas originales.',
                'category_slug' => 'rastras',
                'price_sold' => 350000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Indumentaria ---
            [
                'name' => 'Chaleco Artesanal Bordado con Guardas',
                'description' => 'Chaleco artesanal con bordado de guardas pampeanas en hilo de algodón.',
                'category_slug' => 'indumentaria',
                'price_sold' => 98000,
                'price_sales' => 85000,
                'is_active' => true,
            ],

            // --- Boinas ---
            [
                'name' => 'Boina de Paño',
                'description' => 'Boina clásica de paño en color negro, ideal para el campo y la tradición.',
                'category_slug' => 'boinas',
                'price_sold' => 25000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Collares y Pulseras ---
            [
                'name' => 'Collar Rígido de Alpaca',
                'description' => 'Collar rígido de alpaca con diseño geométrico inspirado en la cultura mapuche.',
                'category_slug' => 'collares-y-pulseras',
                'price_sold' => 48000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Pulsera de Cuero con Pasador de Alpaca',
                'description' => 'Pulsera de cuero curtido con pasador de alpaca grabado.',
                'category_slug' => 'collares-y-pulseras',
                'price_sold' => 18000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Tirador Bordado ---
            [
                'name' => 'Tirador Bordado con Rastra de Alpaca',
                'description' => 'Tirador de cuero con bordado artesanal y rastra de alpaca con monedas.',
                'category_slug' => 'tirador-bordado',
                'price_sold' => 280000,
                'price_sales' => 0,
                'is_active' => true,
            ],

            // --- Más productos para completar el grid ---
            [
                'name' => 'Mate de Calabaza con Virola de Plata',
                'description' => 'Mate de calabaza natural con virola de plata 925 y grabado artesanal.',
                'category_slug' => 'mates',
                'price_sold' => 62000,
                'price_sales' => 58000,
                'is_active' => true,
            ],
            [
                'name' => 'Vaina de Cuero Crudo para Cuchillo',
                'description' => 'Vaina artesanal de cuero crudo con costura a mano y punteras de alpaca.',
                'category_slug' => 'vainas',
                'price_sold' => 35000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Hebilla Ovalada de Alpaca con Flor de Cardo',
                'description' => 'Hebilla ovalada de alpaca con diseño de flor de cardo en relieve.',
                'category_slug' => 'hebillas',
                'price_sold' => 55000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Bombilla de Acero Inoxidable con Filtro',
                'description' => 'Bombilla de acero inoxidable con filtro desmontable y mango torneado.',
                'category_slug' => 'bombillas',
                'price_sold' => 12000,
                'price_sales' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Mate Artesanal de Vidrio con Funda de Cuero',
                'description' => 'Mate de vidrio térmico con funda de cuero cosida a mano.',
                'category_slug' => 'mates',
                'price_sold' => 38000,
                'price_sales' => 32000,
                'is_active' => true,
            ],
        ];

        $skuPrefixes = [
            'mates' => 'MAT',
            'bombillas' => 'BOM',
            'yerberas' => 'YER',
            'cuchillos-damasco' => 'DAM',
            'cuchillos-criollos' => 'CUC',
            'facones' => 'FAC',
            'vainas' => 'VAI',
            'llaveros' => 'LLA',
            'rastras' => 'RAS',
            'hebillas' => 'HEB',
            'indumentaria' => 'IND',
            'boinas' => 'BOI',
            'collares-y-pulseras' => 'COL',
            'tirador-bordado' => 'TIR',
        ];
        $skuCounters = [];
        $ivaRate = 1.21;

        foreach ($products as $data) {
            $categorySlug = $data['category_slug'];
            unset($data['category_slug']);

            $prefix = $skuPrefixes[$categorySlug] ?? 'GEN';
            $skuCounters[$prefix] = ($skuCounters[$prefix] ?? 0) + 1;

            $category = Category::where('slug', $categorySlug)->first();
            $data['slug'] = Str::slug($data['name']);
            $data['sku'] = sprintf('%s%04d', $prefix, $skuCounters[$prefix]);
            $data['category_id'] = $category?->id;
            $data['type'] = ProductTypeEnum::FISICO;
            $data['price_without_tax'] = (int) round($data['price_sold'] / $ivaRate);
            $data['price_provider'] = (int) ($data['price_sold'] * 0.5);
            $data['price_cost'] = (int) ($data['price_sold'] * 0.6);

            Product::create($data);
        }
    }
}
