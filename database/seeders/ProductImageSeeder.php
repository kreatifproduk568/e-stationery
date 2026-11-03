<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'PILOT-G2-BLACK',

                'images' => [
                    [
                        'image_path' => 'products/pilot-g2.jpg',
                        'alt_text' => 'Pulpen Pilot G2',
                        'sort_order' => 1,
                        'is_primary' => true,
                    ],

                    [
                        'image_path' => 'products/pilot-g2-detail.jpg',
                        'alt_text' => 'Detail Pulpen Pilot G2',
                        'sort_order' => 2,
                        'is_primary' => false,
                    ],
                ],
            ],
        ];

        foreach ($products as $item) {

            $product = Product::where('sku', $item['sku'])
                ->firstOrFail();

            foreach ($item['images'] as $image) {

                ProductImage::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'image_path' => $image['image_path'],
                    ],
                    [
                        'alt_text' => $image['alt_text'],
                        'sort_order' => $image['sort_order'],
                        'is_primary' => $image['is_primary'],
                    ]
                );
            }
        }
    }
}