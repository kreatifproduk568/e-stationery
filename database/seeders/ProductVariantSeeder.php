<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $variants = [
            [
                'product_sku' => 'PILOT-G2-BLACK',
                'sku' => 'PILOT-G2-BLACK-01',
                'variant_name' => 'Black',
                'color' => 'Black',
                'material' => null,
                'price' => 18000,
                'cost_price' => 15000,
                'stock' => 50,
            ],
            [
                'product_sku' => 'PILOT-G2-BLACK',
                'sku' => 'PILOT-G2-BLUE-01',
                'variant_name' => 'Blue',
                'color' => 'Blue',
                'material' => null,
                'price' => 18000,
                'cost_price' => 15000,
                'stock' => 40,
            ],
            [
                'product_sku' => 'FAB-PENCIL-2B',
                'sku' => 'FAB-PENCIL-2B-01',
                'variant_name' => 'Standard',
                'color' => null,
                'material' => 'Wood',
                'price' => 5000,
                'cost_price' => 3000,
                'stock' => 100,
            ],
            [
                'product_sku' => 'JOY-NOTE-A5',
                'sku' => 'JOY-NOTE-A5-01',
                'variant_name' => 'A5 Standard',
                'color' => 'Assorted',
                'material' => 'Paper',
                'price' => 15000,
                'cost_price' => 1000,
                'stock' => 75,
            ],
        ];

        foreach ($variants as $data) {
            $product = Product::where('sku', $data['product_sku'])
                ->firstOrFail();

            ProductVariant::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'product_id' => $product->id,
                    'variant_name' => $data['variant_name'],
                    'color' => $data['color'],
                    'material' => $data['material'],
                    'price' => $data['price'],
                    'cost_price' => $data['cost_price'],
                    'stock' => $data['stock'],
                ]
            );
        }
    }
}
