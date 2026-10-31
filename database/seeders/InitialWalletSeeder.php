<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;

class InitialWalletSeeder extends Seeder
{
    /**
     * Seed the initial wallet of the application.
     */
    public function run(): void
    {
        Wallet::firstOrCreate(
            ['name' => 'Dompet Utama'],
            ['is_primary' => true],
        );
    }
}
