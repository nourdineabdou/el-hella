<?php

namespace Database\Seeders;

use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopsSeeder extends Seeder
{
    public function run(): void
    {
        $shops = [
            [
                'shop_number' => Str::uuid()->toString(),
                'name' => 'دكان الحاج سالم',
                'owner_name' => 'الحاج سالم',
                'phone' => '22232200001',
                'wilaya' => 'ولاية نواكشوط الغربية',
                'moughataa' => 'تيول',
                'district' => 'الحي التجاري',
                'address' => 'شارع السوق المركزي',
                'latitude' => 18.0916,
                'longitude' => -15.9785,
                'location_source' => 'admin',
            ],
            [
                'shop_number' => Str::uuid()->toString(),
                'name' => 'بقالة العائلة',
                'owner_name' => 'أم عبد الله',
                'phone' => '22232200002',
                'wilaya' => 'ولاية نواكشوط الجنوبية',
                'moughataa' => 'تيارت',
                'district' => 'حي 16',
                'address' => 'قرب محطة الحافلات',
                'latitude' => 18.0642,
                'longitude' => -15.9724,
                'location_source' => 'admin',
            ],
            [
                'shop_number' => Str::uuid()->toString(),
                'name' => 'سوق الزاوية',
                'owner_name' => 'محمد الأمين',
                'phone' => '22232200003',
                'wilaya' => 'ولاية نواكشوط الشمالية',
                'moughataa' => 'تفرغ زينة',
                'district' => 'الزاوية',
                'address' => 'مقابل البنك',
                'latitude' => 18.1147,
                'longitude' => -15.9828,
                'location_source' => 'admin',
            ],
            [
                'shop_number' => Str::uuid()->toString(),
                'name' => 'محل السمسار',
                'owner_name' => 'علي سيدي',
                'phone' => '22232200004',
                'wilaya' => 'ولاية نواكشوط الغربية',
                'moughataa' => 'أطار',
                'district' => 'المنطقة الصناعية',
                'address' => 'مجمع الأسواق',
                'latitude' => 18.0870,
                'longitude' => -15.9900,
                'location_source' => 'admin',
            ],
            [
                'shop_number' => Str::uuid()->toString(),
                'name' => 'محل التمور',
                'owner_name' => 'خالد',
                'phone' => '22232200005',
                'wilaya' => 'ولاية نواكشوط الجنوبية',
                'moughataa' => 'دار النعيم',
                'district' => 'شارع السوق',
                'address' => 'بجانب المسجد الكبير',
                'latitude' => 18.0500,
                'longitude' => -15.9600,
                'location_source' => 'admin',
            ],
        ];

        foreach ($shops as $s) {
            Shop::firstOrCreate(
                ['shop_number' => $s['shop_number']],
                $s
            );
        }
    }
}
