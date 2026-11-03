<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Alat Tulis',
            'Buku Tulis',
            'Perlengkapan Kantor',
            'Perlengkapan Sekolah',
            'Kertas',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => 'Kategori ' . $name,
                    'is_active' => true,
                ]
            );
        }

    }
}
