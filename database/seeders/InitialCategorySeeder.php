<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class InitialCategorySeeder extends Seeder
{
    /**
     * Seed the initial income and expense categories.
     */
    public function run(): void
    {
        $categories = [
            'income' => [
                'Gaji & Bonus',
                'Modal Masuk',
                'Penjualan',
                'Uang Saku',
                'Lainnya',
            ],
            'expense' => [
                'Belanja & Hiburan',
                'Belanja Barang/Jasa',
                'Gaji & Upah',
                'Makan & Minum',
                'Sosial',
                'Lainnya',
            ],
        ];

        foreach ($categories as $type => $names) {
            foreach ($names as $name) {
                Category::firstOrCreate([
                    'name' => $name,
                    'type' => $type,
                ]);
            }
        }
    }
}
