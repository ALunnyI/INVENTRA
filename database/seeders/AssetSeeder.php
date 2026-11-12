<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        // Cegah bentrok kode aset kalau db:seed dijalankan lagi tanpa migrate:fresh
        if (Asset::withTrashed()->exists()) {
            return;
        }

        $catalog = [
            [
                'category' => 'Laptop', 'label' => 'Laptop', 'prefix' => 'LPT', 'count' => 12,
                'items' => [
                    ['Acer', 'Aspire 3 A315'], ['Lenovo', 'ThinkPad E14'], ['ASUS', 'Vivobook 14'],
                    ['Dell', 'Latitude 3440'], ['HP', 'ProBook 440 G10'],
                ],
            ],
            [
                'category' => 'Monitor', 'label' => 'Monitor', 'prefix' => 'MON', 'count' => 8,
                'items' => [['LG', '24MR400'], ['Dell', 'P2422H'], ['ViewSonic', 'VA2432']],
            ],
            [
                'category' => 'Printer', 'label' => 'Printer', 'prefix' => 'PRN', 'count' => 5,
                'items' => [['Epson', 'L3210'], ['HP', 'LaserJet Pro M404'], ['Canon', 'PIXMA G3010']],
            ],
            [
                'category' => 'Proyektor', 'label' => 'Proyektor', 'prefix' => 'PRJ', 'count' => 4,
                'items' => [['Epson', 'EB-X06'], ['ViewSonic', 'PA503S'], ['BenQ', 'MS550']],
            ],
            [
                'category' => 'Perangkat Jaringan', 'label' => 'Router', 'prefix' => 'NET', 'count' => 6,
                'items' => [['TP-Link', 'Archer C6'], ['MikroTik', 'hAP ac2'], ['Ubiquiti', 'UniFi U6 Lite']],
            ],
            [
                'category' => 'Smartphone & Tablet', 'label' => 'Tablet', 'prefix' => 'MOB', 'count' => 5,
                'items' => [['Samsung', 'Galaxy Tab A9'], ['Xiaomi', 'Redmi Pad'], ['Apple', 'iPad 10th Gen']],
            ],
        ];

        foreach ($catalog as $group) {
            $category = Category::where('name', $group['category'])->first();

            if (! $category) {
                continue; // CategorySeeder belum dijalankan
            }

            for ($i = 1; $i <= $group['count']; $i++) {
                [$brand, $model] = Arr::random($group['items']);

                Asset::factory()->create([
                    'category_id' => $category->id,
                    'asset_code'  => sprintf('%s-%03d', $group['prefix'], $i),
                    'name'        => "{$group['label']} {$brand} {$model}",
                    'brand'       => $brand,
                ]);
            }
        }
    }
}
