<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Sample grocery products distributed to shops.
     *
     * @var array<int, array{code: string, name_ar: string, name_fr: string, unit: string}>
     */
    protected array $products = [
        ['code' => 'SUC', 'name_ar' => 'سكر', 'name_fr' => 'Sucre', 'unit' => 'kg'],
        ['code' => 'RIZ', 'name_ar' => 'أرز', 'name_fr' => 'Riz', 'unit' => 'bag'],
        ['code' => 'HUI', 'name_ar' => 'زيت الطعام', 'name_fr' => 'Huile alimentaire', 'unit' => 'carton'],
        ['code' => 'FAR', 'name_ar' => 'دقيق القمح', 'name_fr' => 'Farine de blé', 'unit' => 'bag'],
        ['code' => 'LAI', 'name_ar' => 'حليب مجفف', 'name_fr' => 'Lait en poudre', 'unit' => 'carton'],
        ['code' => 'THE', 'name_ar' => 'شاي أخضر', 'name_fr' => 'Thé vert', 'unit' => 'carton'],
        ['code' => 'PAT', 'name_ar' => 'معكرونة', 'name_fr' => 'Pâtes alimentaires', 'unit' => 'carton'],
        ['code' => 'CAF', 'name_ar' => 'قهوة', 'name_fr' => 'Café', 'unit' => 'kg'],
    ];

    public function run(): void
    {
        foreach ($this->products as $product) {
            Product::firstOrCreate(
                ['code' => $product['code']],
                [
                    'name_ar' => $product['name_ar'],
                    'name_fr' => $product['name_fr'],
                    'unit' => $product['unit'],
                    'is_active' => true,
                ]
            );
        }
    }
}
