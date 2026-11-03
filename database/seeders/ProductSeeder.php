<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $products = [
            [
                'sku' => 'PILOT-G2-BLACK',
                'name' => 'Pilot G2 Gel Pen Black',
                'category' => 'alat-tulis',
                'brand' => 'pilot',
                // 'price' => 18000,
                // 'cost_price' => 12000,
                'description' => 'Pulpen gel tinta hitam.',
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'sku' => 'FAB-PENCIL-2B',
                'name' => 'Faber-Castell Pencil 2B',
                'category' => 'alat-tulis',
                'brand' => 'faber-castell',
                // 'price' => 5000,
                // 'cost_price' => 3000,
                'description' => 'Pensil 2B untuk kebutuhan sekolah.',
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'sku' => 'JOY-NOTE-A5',
                'name' => 'Joyko Notebook A5',
                'category' => 'buku-tulis',
                'brand' => 'joyko',
                // 'price' => 15000,
                // 'cost_price' => 10000,
                'description' => 'Buku catatan ukuran A5.',
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'sku' => 'JOY-STAPLER-01',
                'name' => 'Joyko Stapler',
                'category' => 'perlengkapan-kantor',
                'brand' => 'joyko',
                // 'price' => 22000,
                // 'cost_price' => 15000,
                'description' => 'Stapler untuk kebutuhan kantor.',
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        foreach ($products as $data) {
            $category = Category::where('slug', $data['category'])
                ->firstOrFail();

            $brand = Brand::where('slug', $data['brand'])
                ->firstOrFail();

            Product::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => $data['description'],
                    // 'price' => $data['price'],
                    // 'cost_price' => $data['cost_price'],
                    'is_active' => $data['is_active'],
                    'is_featured' => $data['is_featured'],
                ]
            );
        }
    }
}
